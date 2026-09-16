<?php

namespace App\Services\Hosting;

use App\Models\HostingServer;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use RuntimeException;
use Illuminate\Support\Str;

class CustomSshAdapter implements HostingProviderInterface
{
    private function connect(HostingServer $server): SSH2
    {
        $ssh = new SSH2($server->ip_address, $server->ssh_port ?? 22);

        if (! file_exists($server->ssh_key_path)) {
            throw new RuntimeException("SSH key not found: {$server->ssh_key_path}");
        }

        $key = PublicKeyLoader::load(file_get_contents($server->ssh_key_path));

        if (! $ssh->login($server->ssh_user, $key)) {
            throw new RuntimeException("SSH login failed for {$server->ssh_user}@{$server->ip_address}");
        }

        return $ssh;
    }

    private function exec(HostingServer $server, string $command): string
    {
        $ssh = $this->connect($server);
        $output = $ssh->exec("sudo {$command} 2>&1");

        return trim($output);
    }

    private function writeFile(HostingServer $server, string $path, string $content): void
    {
        $ssh = $this->connect($server);
        $escaped = escapeshellarg($content);
        $ssh->exec("sudo tee {$path} > /dev/null << 'HOSTKUFILE'\n{$content}\nHOSTKUFILE");
    }

    private function basePath(HostingServer $server): string
    {
        return rtrim($server->base_path ?: '/var/www', '/');
    }

    private function poolName(HostingServer $server, string $username): string
    {
        $version = $server->php_version ?: '8.3';

        return "/etc/php/{$version}/fpm/pool.d/{$username}.conf";
    }

    private function nginxAvailable(HostingServer $server, string $domain): string
    {
        return "/etc/nginx/sites-available/{$domain}.conf";
    }

    private function nginxEnabled(HostingServer $server, string $domain): string
    {
        return "/etc/nginx/sites-enabled/{$domain}.conf";
    }

    public function createAccount(HostingServer $server, array $config): array
    {
        $domain = $config['domain'];
        $username = $config['username'];
        $password = $config['password'] ?? Str::password(16);
        $base = $this->basePath($server);
        $home = "{$base}/{$username}";
        $dbName = 'h_'.$username;
        $dbPass = Str::password(24);
        $version = $server->php_version ?: '8.3';

        $this->exec($server, "useradd -m -d {$home} -s /bin/false {$username}");
        $this->exec($server, "mkdir -p {$home}/{public_html,logs,backup}");
        $this->exec($server, "chown -R {$username}:{$username} {$home}");

        $ssh = $this->connect($server);
        $ssh->exec("sudo sh -c 'echo \"{$username}:{$password}\" | chpasswd' 2>&1");

        $this->exec($server, "mysql -e \"CREATE DATABASE IF NOT EXISTS {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY '{$dbPass}'; GRANT ALL PRIVILEGES ON {$dbName}.* TO '{$username}'@'localhost'; FLUSH PRIVILEGES;\"");

        $nginxConfig = $this->buildNginxConfig($server, $domain, $username, $home);
        $this->writeFile($server, $this->nginxAvailable($server, $domain), $nginxConfig);
        $this->exec($server, "ln -sf {$this->nginxAvailable($server, $domain)} {$this->nginxEnabled($server, $domain)}");

        $poolConfig = $this->buildPhpFpmPool($version, $username);
        $this->writeFile($server, $this->poolName($server, $username), $poolConfig);

        $this->exec($server, "systemctl reload nginx php{$version}-fpm");

        if ($server->ssl_email) {
            $this->exec($server, "certbot --nginx -d {$domain} -d www.{$domain} --non-interactive --agree-tos -m {$server->ssl_email} 2>&1 || true");
        }

        return [
            'username' => $username,
            'password' => $password,
            'domain' => $domain,
            'home' => $home,
            'db_name' => $dbName,
            'db_user' => $username,
            'db_pass' => $dbPass,
        ];
    }

    public function suspendAccount(HostingServer $server, string $username): array
    {
        $this->exec($server, "usermod --lock {$username}");

        return ['success' => true, 'username' => $username, 'action' => 'suspended'];
    }

    public function unsuspendAccount(HostingServer $server, string $username): array
    {
        $this->exec($server, "usermod --unlock {$username}");

        return ['success' => true, 'username' => $username, 'action' => 'unsuspended'];
    }

    public function terminateAccount(HostingServer $server, string $username): array
    {
        $home = "{$this->basePath($server)}/{$username}";
        $poolFile = $this->poolName($server, $username);
        $version = $server->php_version ?: '8.3';

        $this->exec($server, "userdel -r {$username} 2>/dev/null || true");
        $this->exec($server, "rm -rf {$home}");
        $this->exec($server, "rm -f {$poolFile}");
        $this->exec($server, "rm -f /etc/nginx/sites-available/*{$username}* /etc/nginx/sites-enabled/*{$username}*");
        $this->exec($server, "mysql -e \"DROP DATABASE IF EXISTS h_{$username}; DROP USER IF EXISTS '{$username}'@'localhost';\"");
        $this->exec($server, "systemctl reload nginx php{$version}-fpm");

        return ['success' => true, 'username' => $username, 'action' => 'terminated'];
    }

    public function changePassword(HostingServer $server, string $username, string $password): array
    {
        $ssh = $this->connect($server);
        $ssh->exec("sudo sh -c 'echo \"{$username}:{$password}\" | chpasswd'");

        return ['success' => true, 'username' => $username];
    }

    public function getAccountInfo(HostingServer $server, string $username): array
    {
        $home = "{$this->basePath($server)}/{$username}";
        $diskUsage = $this->exec($server, "du -sh {$home}/public_html 2>/dev/null || echo 'N/A'");

        return [
            'username' => $username,
            'home' => $home,
            'disk_usage' => $diskUsage,
        ];
    }

    public function getResourceUsage(HostingServer $server, string $username): array
    {
        $disk = $this->exec($server, "du -sb {$this->basePath($server)}/{$username}/public_html 2>/dev/null || echo '0'");
        $dbSize = $this->exec($server, "mysql -N -e \"SELECT COALESCE(SUM(data_length + index_length), 0) FROM information_schema.tables WHERE table_schema='h_{$username}'\"");

        return [
            'disk_bytes' => (int) explode("\t", $disk)[0],
            'db_bytes' => (int) $dbSize,
        ];
    }

    private function buildNginxConfig(HostingServer $server, string $domain, string $username, string $home): string
    {
        $version = $server->php_version ?: '8.3';

        return <<<NGINX
server {
    listen 80;
    server_name {$domain} www.{$domain};
    root {$home}/public_html;
    index index.php index.html;

    access_log {$home}/logs/access.log;
    error_log {$home}/logs/error.log;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php{$version}-fpm-{$username}.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX;
    }

    private function buildPhpFpmPool(string $version, string $username): string
    {
        return <<<PHPFPM
[{$username}]
user = {$username}
group = {$username}
listen = /run/php/php{$version}-fpm-{$username}.sock
listen.owner = {$username}
listen.group = www-data
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 5
php_admin_value[open_basedir] = /var/www/{$username}:/tmp
PHPFPM;
    }
}
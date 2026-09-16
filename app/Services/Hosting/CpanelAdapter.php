<?php

namespace App\Services\Hosting;

use App\Models\HostingServer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CpanelAdapter implements HostingProviderInterface
{
    public function createAccount(HostingServer $server, array $config): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 60])
            ->withHeader('Authorization', "whm {$server->api_username}:{$server->api_token}")
            ->get("{$server->api_url}/json-api/createacct", [
                'username' => $config['username'],
                'domain' => $config['domain'],
                'password' => $config['password'] ?? Str::password(16),
                'plan' => $config['package'] ?? 'default',
                'featurelist' => 'default',
                'quota' => $config['disk_quota_mb'] ?? 1024,
                'maxftp' => $config['max_ftp'] ?? 1,
                'maxsql' => $config['max_databases'] ?? 1,
                'maxpop' => $config['max_emails'] ?? 1,
                'maxsub' => $config['max_subdomains'] ?? 0,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException("cPanel account creation failed: {$response->body()}");
        }

        $result = $response->json();

        if (($result['result'][0]['status'] ?? 0) !== 1) {
            throw new \RuntimeException('cPanel error: '.($result['result'][0]['statusmsg'] ?? 'Unknown'));
        }

        return $result;
    }

    public function suspendAccount(HostingServer $server, string $username): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 30])
            ->withHeader('Authorization', "whm {$server->api_username}:{$server->api_token}")
            ->get("{$server->api_url}/json-api/suspendacct", ['user' => $username]);

        return $response->json();
    }

    public function unsuspendAccount(HostingServer $server, string $username): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 30])
            ->withHeader('Authorization', "whm {$server->api_username}:{$server->api_token}")
            ->get("{$server->api_url}/json-api/unsuspendacct", ['user' => $username]);

        return $response->json();
    }

    public function terminateAccount(HostingServer $server, string $username): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 30])
            ->withHeader('Authorization', "whm {$server->api_username}:{$server->api_token}")
            ->get("{$server->api_url}/json-api/removeacct", ['user' => $username]);

        return $response->json();
    }

    public function changePassword(HostingServer $server, string $username, string $password): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 30])
            ->withHeader('Authorization', "whm {$server->api_username}:{$server->api_token}")
            ->get("{$server->api_url}/json-api/passwd", [
                'user' => $username,
                'pass' => $password,
            ]);

        return $response->json();
    }

    public function getAccountInfo(HostingServer $server, string $username): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 15])
            ->withHeader('Authorization', "whm {$server->api_username}:{$server->api_token}")
            ->get("{$server->api_url}/json-api/accountsummary", ['user' => $username]);

        return $response->json();
    }

    public function getResourceUsage(HostingServer $server, string $username): array
    {
        $response = Http::withOptions(['verify' => false, 'timeout' => 15])
            ->withHeader('Authorization', "whm {$server->api_username}:{$server->api_token}")
            ->get("{$server->api_url}/json-api/showbw", ['user' => $username]);

        return $response->json();
    }
}

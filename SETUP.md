# Setup Guide — Platform HostKu

Panduan setup lengkap untuk menjalankan platform HostKu dengan custom hosting server (Nginx + MySQL + PHP-FPM + SSL).

---

## Arsitektur

```
┌─────────────────────────────┐       SSH (port 22)       ┌──────────────────────────────┐
│       APP SERVER             │ ◄──────────────────────► │       HOSTING SERVER          │
│                              │                          │                              │
│  Laravel 13 + PHP 8.4       │                          │  Ubuntu 22.04 / 24.04        │
│  MySQL / SQLite              │                          │  Nginx + PHP-FPM + MySQL     │
│  Redis                       │                          │  Certbot (Let's Encrypt)     │
│                              │                          │                              │
│  Bisa jalan di 1 server     │                          │  /var/www/{customer}/        │
│  atau 2 server terpisah     │                          │  /etc/nginx/sites-available/ │
└─────────────────────────────┘                          └──────────────────────────────┘
```

> **Catatan:** Kalau baru mulai, kamu bisa jalankan App + Hosting di **1 server yang sama**. Semua langkah di guide ini tetap berlaku.

---

## Bagian 1 — App Server Setup

### 1.1 Requirements

- Ubuntu 22.04 / 24.04 LTS
- **PHP 8.4+** (wajib — Laravel 13 + Symfony 8.x butuh PHP >= 8.4.1)
- MySQL 8.0+ / MariaDB 10.11+
- Redis 7+ (untuk queue)
- Composer 2
- Node.js 20+ & npm

### 1.2 Install Software

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# === PENTING: Tambah PPA ondrej/php dulu ===
# PHP 8.4 tidak ada di default repo Ubuntu. Wajib tambah PPA ini.
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.4 + extensions
sudo apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-mysql php8.4-redis \
    php8.4-curl php8.4-mbstring php8.4-xml php8.4-zip php8.4-bcmath \
    php8.4-gd php8.4-intl unzip git curl

# Set PHP 8.4 sebagai default CLI
sudo update-alternatives --set php /usr/bin/php8.4

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 1.3 Database Setup

```bash
sudo mysql -u root
```

```sql
CREATE DATABASE hostku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hostku'@'localhost' IDENTIFIED BY 'PASSWORD_AMAN_KAMU';
GRANT ALL PRIVILEGES ON hostku.* TO 'hostku'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 1.4 Clone & Setup Project

```bash
# Clone / upload project
cd /var/www
git clone https://github.com/ehsandisini08-del/hostku.git hostku
cd hostku

# Copy env
cp .env.example .env

# Edit .env
nano .env
```

**Isi .env (minimal untuk production):**
```env
APP_NAME=HostKu
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.hostku.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hostku
DB_USERNAME=hostku
DB_PASSWORD=PASSWORD_AMAN_KAMU

REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=emailkamu@gmail.com
MAIL_PASSWORD=app_password_google
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hostku.id
MAIL_FROM_NAME="HostKu"

# Payment (isi kalau sudah daftar)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
XENDIT_API_KEY=
DOKU_CLIENT_ID=
```

### 1.5 Install Dependencies & Build

```bash
# === PENTING: Baca baik-baik ===
# Proyek ini pakai PHP 8.4+ dan Laravel 13 dengan Symfony 8.x.
# Pastikan php -v menunjukkan 8.4.x sebelum lanjut.

php -v
# Harus: PHP 8.4.x

# Install PHP packages (production mode)
# --no-dev: skip dev packages (pest, pint, larastan, laravel/boost, dll)
# JANGAN pakai --no-scripts: script post-autoload-dump menjalankan
# `php artisan package:discover` yang MENULIS ULANG bootstrap/cache/packages.php
# sesuai paket yang benar-benar terpasang (tanpa Boost).
composer install --no-dev

# Bersihkan cache discovery lama (mencegah "BoostServiceProvider not found")
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php

# Regenerate manifest paket
php artisan package:discover

# Generate app key
php artisan key:generate

# === PENTING: Install Faker untuk seeding ===
# fakerphp/faker ada di require-dev, tapi dibutuhkan
# saat seeding di production. Install manual.
# --no-scripts agar post-update-cmd (boost:update) tidak error di production.
composer require fakerphp/faker --no-scripts

# Install & build frontend
npm install
npm run build

# Run migrations & seed
php artisan migrate --seed

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.6 Nginx Config (App Server)

```bash
sudo nano /etc/nginx/sites-available/hostku-app
```

```nginx
server {
    listen 80;
    server_name app.hostku.id;
    root /var/www/hostku/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/hostku-app /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 1.7 Queue Worker (Supervisor)

```bash
sudo apt install -y supervisor

sudo nano /etc/supervisor/conf.d/hostku-worker.conf
```

```ini
[program:hostku-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hostku/artisan queue:work redis --sleep=3 --tries=1 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hostku/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### 1.8 Scheduler (Cron)

```bash
sudo crontab -u www-data -e
```

```
* * * * * php /var/www/hostku/artisan schedule:run >> /dev/null 2>&1
```

### 1.9 SSL (App Server)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d app.hostku.id
```

---

## Bagian 2 — Hosting Server Setup

> **Jika App & Hosting di server yang sama:** lewati Bagian 3 (SSH Key), langsung ke 2.1. Gunakan `localhost` sebagai IP di form admin.

### 2.1 Install Software

```bash
sudo apt update && sudo apt upgrade -y

# === Wajib: Tambah PPA ondrej/php ===
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Web server + PHP 8.4 + MySQL + SSL
sudo apt install -y nginx mysql-server php8.4-fpm php8.4-cli \
    php8.4-mysql php8.4-curl php8.4-mbstring php8.4-xml \
    php8.4-zip php8.4-gd php8.4-intl \
    certbot python3-certbot-nginx

# Enable services
sudo systemctl enable nginx mysql php8.4-fpm
sudo systemctl start nginx mysql php8.4-fpm
```

### 2.2 MySQL Setup

```bash
sudo mysql -u root
```

```sql
-- Buat user provisioning (untuk remote SSH commands)
CREATE USER 'hostku_provision'@'localhost' IDENTIFIED BY 'DB_PASSWORD_AMAN';
GRANT CREATE, DROP, RELOAD ON *.* TO 'hostku_provision'@'localhost';
GRANT ALL PRIVILEGES ON `h\_%`.* TO 'hostku_provision'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2.3 Buat User Provisioning (Linux)

```bash
# User khusus untuk provisioning via SSH
sudo useradd -m -s /bin/bash hostku-provision
sudo passwd hostku-provision
# Masukkan password (tidak perlu diingat, pakai SSH key nanti)
```

### 2.4 Beri Sudo Access Terbatas

```bash
sudo nano /etc/sudoers.d/hostku
```

```
# Sudo access untuk provisioning otomatis
hostku-provision ALL=(ALL) NOPASSWD: /usr/sbin/useradd, /usr/sbin/userdel, /usr/sbin/usermod
hostku-provision ALL=(ALL) NOPASSWD: /usr/bin/passwd, /bin/mkdir, /bin/chown, /bin/chmod
hostku-provision ALL=(ALL) NOPASSWD: /usr/bin/ln, /bin/rm, /bin/mv, /usr/bin/tee, /usr/bin/sh
hostku-provision ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx
hostku-provision ALL=(ALL) NOPASSWD: /bin/systemctl reload php8.4-fpm, /bin/systemctl reload php*fpm
hostku-provision ALL=(ALL) NOPASSWD: /usr/bin/certbot, /usr/bin/mysql, /usr/bin/du
```

```bash
sudo chmod 440 /etc/sudoers.d/hostku
```

### 2.5 Struktur Folder Awal

```bash
# Buat folder root untuk semua customer hosting
sudo mkdir -p /var/www
sudo chown root:root /var/www

# Verifikasi Nginx sites-available ada
ls /etc/nginx/sites-available/
ls /etc/nginx/sites-enabled/
```

### 2.6 PHP-FPM Pool Default

Pastikan PHP-FPM pool default berjalan:

```bash
sudo systemctl status php8.4-fpm
ls /etc/php/8.4/fpm/pool.d/
```

Folder `/etc/php/8.4/fpm/pool.d/` harus writable oleh `hostku-provision`:

```bash
sudo chmod 755 /etc/php/8.4/fpm/pool.d/
```

---

## Bagian 3 — SSH Key Setup (App Server ↔ Hosting Server)

> **Jika App & Hosting di server yang sama:** lewati langkah SSH key. Di form admin, gunakan `localhost` dan user system langsung (set `ssh_host=127.0.0.1`).

### 3.1 Generate SSH Key (DI APP SERVER)

```bash
# Di App Server, buat folder keys
mkdir -p /var/www/hostku/storage/keys
chmod 700 /var/www/hostku/storage/keys

# Generate ed25519 key (lebih aman dari RSA)
ssh-keygen -t ed25519 -f /var/www/hostku/storage/keys/hostku_provision -N ""

# Verifikasi
ls -la /var/www/hostku/storage/keys/
# Output: hostku_provision (private) + hostku_provision.pub (public)
```

### 3.2 Copy Public Key ke Hosting Server

```bash
# Dari App Server, copy public key ke hosting server
ssh-copy-id -i /var/www/hostku/storage/keys/hostku_provision.pub hostku-provision@IP-HOSTING-SERVER

# Atau manual:
# 1. Baca public key
cat /var/www/hostku/storage/keys/hostku_provision.pub

# 2. Di HOSTING SERVER, paste ke authorized_keys
sudo mkdir -p /home/hostku-provision/.ssh
sudo nano /home/hostku-provision/.ssh/authorized_keys
# Paste isi public key, simpan

sudo chmod 700 /home/hostku-provision/.ssh
sudo chmod 600 /home/hostku-provision/.ssh/authorized_keys
sudo chown -R hostku-provision:hostku-provision /home/hostku-provision/.ssh
```

### 3.3 Test SSH Connection

```bash
# Dari App Server, test koneksi
ssh -i /var/www/hostku/storage/keys/hostku_provision hostku-provision@IP-HOSTING-SERVER "sudo whoami"

# Harus output: root
```

### 3.4 Firewall (Hosting Server)

```bash
# Buka port yang diperlukan
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw allow 3306/tcp  # MySQL (hanya dari IP App Server!)
sudo ufw enable
```

---

## Bagian 4 — Tambah Hosting Server ke Admin Panel

### 4.1 Login Admin

Email dan password default dari seeder:

```
Email: admin@hostku.id
Password: password
```

### 4.2 Buka Halaman Hosting Servers

Klik sidebar: **Hosting Servers** → klik **Add Server**

### 4.3 Pilih Tipe Provisioning

Kamu akan lihat 3 pilihan di dropdown **Provisioning Type**:

#### Opsi A — Custom SSH (Nginx + PHP-FPM)

Pilih `Custom (SSH + Nginx + PHP-FPM)`, isi:

| Field | Contoh Isi | Keterangan |
|---|---|---|
| Name | `Server DC1` | Label untuk server ini |
| Hostname | `srv1.hostku.id` | Hostname server hosting |
| IP Address | `192.168.1.100` | IP server hosting |
| SSH Port | `22` | Default SSH port |
| SSH User | `hostku-provision` | User provisioning |
| SSH Key Path | `/var/www/hostku/storage/keys/hostku_provision` | Path private key di App Server |
| Web Server | `nginx` | Pilih nginx |
| PHP Version | `8.4` | Versi PHP terinstall |
| Base Path | `/var/www` | Root folder customer |
| SSL Email | `admin@hostku.id` | Untuk Let's Encrypt |

#### Opsi B — cPanel/WHM

Pilih `cPanel / WHM`, isi API URL, API Token, API Username.

#### Opsi C — DirectAdmin

Pilih `DirectAdmin`, isi API URL, API Token, API Username.

### 4.4 Klik Save Server

Server muncul di list dengan type badge.

---

## Bagian 5 — Tambah Hosting Plan (Produk)

### 5.1 Buka Halaman Products

Klik sidebar: **Products** → klik **Add**

### 5.2 Buat 3 Paket Hosting

**Paket Starter:**
| Field | Value |
|---|---|
| Type | Hosting |
| Name | Starter Hosting |
| Disk MB | 5120 |
| Max Websites | 1 |
| Max Databases | 1 |
| Max Emails | 1 |
| **Price (monthly)** | 25000 |
| **Price (annually)** | 250000 |

**Paket Business:**
| Field | Value |
|---|---|
| Name | Business Hosting |
| Disk MB | 20480 |
| Max Websites | 5 |
| Max Databases | 5 |
| Max Emails | 10 |
| **Price (monthly)** | 75000 |
| **Price (annually)** | 750000 |

**Paket Pro:**
| Field | Value |
|---|---|
| Name | Pro Hosting |
| Disk MB | 51200 |
| Max Websites | 0 (unlimited) |
| Max Databases | 0 (unlimited) |
| Max Emails | 0 (unlimited) |
| **Price (monthly)** | 150000 |
| **Price (annually)** | 1500000 |

---

## Bagian 6 — Test Provisioning

### 6.1 Buat Order (Manual Test)

Cara manual test provisioning:

```bash
# Di App Server, masuk tinker
php artisan tinker

# Cari hosting server aktif
$server = App\Models\HostingServer::where('is_active', true)->first();

# Test SSH connection (custom_ssh)
$adapter = new App\Services\Hosting\CustomSshAdapter;
$result = $adapter->createAccount($server, [
    'username' => 'testclient123',
    'domain' => 'testclient123.hostku.id',
    'password' => 'TestPass123!',
]);

# Lihat hasiluser
print_r($result);

# Test terminate (bersihin)
$adapter->terminateAccount($server, 'testclient123');
```

### 6.2 Verifikasi di Hosting Server

```bash
# Cek user Linux ada
id testclient123

# Cek folder
ls -la /var/www/testclient123/

# Cek Nginx config
cat /etc/nginx/sites-available/testclient123.hostku.id.conf

# Cek PHP-FPM pool
cat /etc/php/8.4/fpm/pool.d/testclient123.conf

# Cek database
sudo mysql -e "SHOW DATABASES LIKE 'h_testclient123';"
```

---

## Bagian 7 — Troubleshooting

### "Composer install gagal: PHP version tidak memenuhi" — PHP 8.3

**Penyebab:** Server menjalankan PHP 8.3, tapi proyek ini butuh PHP 8.4+. Laravel 13 + Symfony 8.x minimal PHP 8.4.1.

**Solusi:** Upgrade PHP ke 8.4.
```bash
# Cek versi PHP saat ini
php -v

# Kalau masih 8.3, jalankan:
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-mysql \
    php8.4-curl php8.4-mbstring php8.4-xml php8.4-zip \
    php8.4-bcmath php8.4-gd php8.4-intl php8.4-redis

# Set PHP 8.4 sebagai default
sudo update-alternatives --set php /usr/bin/php8.4

# Update socket di Nginx config: php8.3-fpm → php8.4-fpm

# Restart
sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx
```

### "Unable to locate package php8.4-*" — PHP 8.4 tidak ditemukan

**Penyebab:** Ubuntu default repo tidak menyediakan PHP 8.4. Wajib tambah PPA ondrej/php.

**Solusi:**
```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-mysql \
    php8.4-curl php8.4-mbstring php8.4-xml php8.4-zip \
    php8.4-bcmath php8.4-gd php8.4-intl
```

### "Class Laravel\Boost\BoostServiceProvider not found" — saat artisan command

**Penyebab:** `laravel/boost` hanya ada di `require-dev`, jadi tidak terinstall saat `--no-dev`. Tapi file cache discovery `bootstrap/cache/packages.php` masih mencantumkan `BoostServiceProvider` dari install dev sebelumnya. Cache ini basi karena langkah lama memakai `composer install --no-dev --no-scripts` yang melewatkan `package:discover`.

**Solusi:** Regenerate cache discovery sesuai paket production yang terpasang.
```bash
cd /var/www/hostku

# 1. Hapus cache yang basi
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
      bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/events.php

# 2. Regenerate manifest (tanpa --no-scripts supaya package:discover jalan)
composer install --no-dev

# 3. Optimasi ulang
php artisan config:clear
php artisan optimize
```

### "Cannot redeclare class AdminUserSeeder" — saat seeding

**Penyebab:** File `database/seeders/AdminUserSeeder.php` kehilangan deklarasi `namespace Database\Seeders;`, sehingga kelas ter-declare di global namespace dan autoloader PSR-4 mengikut file yang sama dua kali.

**Solusi:** Pastikan baris `namespace Database\Seeders;` ada di bawah `<?php` pada file seeder tersebut (sama seperti `RoleSeeder`/`ProductSeeder`). Setelah itu jalankan ulang:
```bash
php artisan migrate --seed
```

### "Call to undefined function fake()" — Faker tidak terinstall

**Penyebab:** `fakerphp/faker` ada di `require-dev`, tapi dibutuhkan saat `php artisan db:seed` atau `migrate --seed` karena factory menggunakan fungsi `fake()`.

**Solusi:** Install Faker sebagai production dependency.
```bash
composer require fakerphp/faker
```
Lalu jalankan ulang:
```bash
php artisan migrate --seed
```

### SSH Connection Failed

```bash
# Test manual SSH
ssh -vvv -i /var/www/hostku/storage/keys/hostku_provision hostku-provision@IP-SERVER

# Cek permission
ls -la /var/www/hostku/storage/keys/hostku_provision
# Harus: -rw------- (600)

# Cek authorized_keys di hosting server
sudo cat /home/hostku-provision/.ssh/authorized_keys
```

### useradd: Permission Denied

```bash
# Cek sudoers
sudo cat /etc/sudoers.d/hostku

# Test manual sudo
sudo -u hostku-provision sudo useradd --help
```

### Nginx Config Gak Reload

```bash
# Cek syntax
sudo nginx -t

# Cek permission
sudo chmod 755 /etc/nginx/sites-available/
sudo chmod 755 /etc/nginx/sites-enabled/
```

### MySQL Access Denied

```bash
# Test manual
sudo mysql -u hostku_provision -p -e "SHOW DATABASES;"

# Cek privileges
sudo mysql -e "SHOW GRANTS FOR 'hostku_provision'@'localhost';"
```

### Certbot SSL Gagal

```bash
# Pastikan domain pointing ke IP server
dig testclient123.hostku.id

# Pastikan port 80 terbuka
sudo ufw status

# Test manual certbot
sudo certbot certonly --webroot -w /var/www/testclient123/public_html -d testclient123.hostku.id
```

---

## Bagian 8 — Setup 1 Server (App + Hosting di mesin sama)

Kalau kamu baru mulai dan mau jalankan semuanya di 1 server:

### 8.1 Jalankan Bagian 1 + Bagian 2 di server yang sama

Semua software (MySQL, Nginx, PHP-FPM, Redis) cukup install 1×.

### 8.2 Skip Bagian 3 (SSH Key)

Tidak perlu SSH key karena App dan Hosting di mesin sama.

### 8.3 Di Form Admin (Bagian 4):

Gunakan **Custom SSH** dengan:
- **IP Address:** `127.0.0.1`
- **SSH User:** `hostku-provision`
- **SSH Key Path:** `/var/www/hostku/storage/keys/hostku_provision`

Atau kamu bisa buat dedicated hosting server entry dengan IP `127.0.0.1`.

### 8.4 Pastikan User Provisioning Bisa Sudo

```bash
# Test dari App Server
sudo -u www-data ssh -i /var/www/hostku/storage/keys/hostku_provision hostku-provision@127.0.0.1 "sudo whoami"
```

---

## Ringkasan Checklist

- [ ] App Server: PHP 8.4, MySQL, Redis, Nginx terinstall
- [ ] App Server: `php -v` menunjukkan 8.4.x (bukan 8.3)
- [ ] App Server: Laravel ter-clone, .env terisi
- [ ] App Server: `composer require fakerphp/faker --no-scripts` (wajib untuk seeding)
- [ ] App Server: `composer install --no-dev` + `php artisan package:discover` sukses (tanpa `--no-scripts`)
- [ ] App Server: `php artisan migrate --seed` sukses
- [ ] App Server: Nginx config + SSL jalan
- [ ] App Server: Supervisor queue worker jalan
- [ ] App Server: Cron scheduler jalan
- [ ] Hosting Server: Nginx, PHP 8.4-FPM, MySQL, Certbot terinstall
- [ ] Hosting Server: User `hostku-provision` dibuat + sudo access
- [ ] Hosting Server: MySQL user `hostku_provision` dibuat
- [ ] SSH Key: Generate di App Server → public key di Hosting Server
- [ ] SSH Test: `ssh -i key user@host "sudo whoami"` berhasil
- [ ] Firewall: Port 22, 80, 443 terbuka di Hosting Server
- [ ] Admin: Hosting Server ditambahkan dengan type `custom_ssh`
- [ ] Admin: Hosting Plans dibuat (minimal 1 paket)
- [ ] Test: Provisioning manual via tinker berhasil

**Setup selesai!** Platform HostKu siap menerima order hosting.
# Deployment Guide

## Server Requirements

- Ubuntu 22.04 / 24.04 LTS
- PHP 8.3+
- MySQL 8.0+ / MariaDB 10.11+
- Redis 7+
- Nginx 1.24+
- Supervisor
- Composer 2
- Node.js 20+ & pnpm
- Git

---

## Server Setup

### 1. Initial Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y \
    nginx \
    mysql-server \
    redis-server \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-redis \
    php8.3-curl \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-gd \
    php8.3-intl \
    unzip \
    git \
    supervisor \
    certbot \
    python3-certbot-nginx
```

### 2. Database Setup

```bash
sudo mysql -u root
```

```sql
CREATE DATABASE hosting_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hosting_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON hosting_platform.* TO 'hosting_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Redis Setup

```bash
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

Verify:
```bash
redis-cli ping   # Should return PONG
```

### 4. Create Application User & Directory

```bash
sudo useradd -m -s /bin/bash app
sudo mkdir -p /var/www/hosting-platform
sudo chown -R app:app /var/www/hosting-platform
```

---

## Application Deployment

### 1. Clone Repository

```bash
sudo -u app git clone <repository-url> /var/www/hosting-platform
cd /var/www/hosting-platform
```

### 2. Environment Configuration

```bash
sudo -u app cp .env.example .env
sudo -u app php artisan key:generate
```

Edit `.env` with production values:
```env
APP_NAME="Your Hosting Platform"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hosting_platform
DB_USERNAME=hosting_user
DB_PASSWORD=strong_password_here

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your Platform"

# Provider credentials
PROXMOX_HOST=https://proxmox.yourdomain.com:8006
PROXMOX_TOKEN_ID=root@pam!token-name
PROXMOX_TOKEN_SECRET=your-token-secret

MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_MERCHANT_ID=Gxxxxx

XENDIT_API_KEY=xnd_development_xxxxx
XENDIT_CALLBACK_TOKEN=your-callback-token

DOKU_CLIENT_ID=BRN-xxxxx
DOKU_SECRET_KEY=SK-xxxxx

DOMAIN_PROVIDER_API_URL=https://api.registrar.com
DOMAIN_PROVIDER_USERNAME=your-username
DOMAIN_PROVIDER_PASSWORD=your-password
```

### 3. Install Dependencies & Build

```bash
# PHP dependencies
sudo -u app composer install --no-dev --optimize-autoloader

# Frontend build
sudo -u app pnpm install
sudo -u app pnpm run build
```

### 4. Database Migration & Seed

```bash
sudo -u app php artisan migrate --force
sudo -u app php artisan db:seed --force
```

### 5. Storage & Permissions

```bash
sudo -u app php artisan storage:link
sudo chmod -R 775 /var/www/hosting-platform/storage
sudo chmod -R 775 /var/www/hosting-platform/bootstrap/cache
```

### 6. Optimize

```bash
sudo -u app php artisan config:cache
sudo -u app php artisan route:cache
sudo -u app php artisan view:cache
sudo -u app php artisan event:cache
```

---

## Nginx Configuration

Create `/etc/nginx/sites-available/hosting-platform`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/hosting-platform/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # API rate limiting
    location /api/ {
        limit_req zone=api burst=30 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Webhook: higher rate limit
    location /api/v1/webhooks/ {
        limit_req zone=webhook burst=100 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
}

# Rate limiting zones (in http block)
# limit_req_zone $binary_remote_addr zone=api:10m rate=30r/m;
# limit_req_zone $binary_remote_addr zone=webhook:10m rate=60r/m;
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/hosting-platform /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### SSL (Let's Encrypt)

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
sudo certbot renew --dry-run  # Test auto-renewal
```

---

## Supervisor Configuration

Create `/etc/supervisor/conf.d/hosting-platform.conf`:

```ini
[program:hosting-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hosting-platform/artisan queue:work redis --queue=default,provisioning,mail,maintenance,sync --sleep=3 --tries=1 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=app
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/hosting-platform/storage/logs/queue-worker.log
stopwaitsecs=3600

[program:hosting-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hosting-platform/artisan schedule:work
autostart=true
autorestart=true
user=app
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/hosting-platform/storage/logs/scheduler.log
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### Supervisor Commands

```bash
sudo supervisorctl status                    # Check all processes
sudo supervisorctl restart hosting-queue:*   # Restart queue workers
sudo supervisorctl tail hosting-queue:00     # View logs
```

---

## Queue Configuration

### Queue Names & Workers

| Queue | Workers | Purpose |
|---|---|---|
| `default` | 2 | General async tasks |
| `provisioning` | 2 | VPS/Hosting/Domain provisioning |
| `mail` | 1 | Email sending |
| `maintenance` | 1 | Suspend/terminate jobs |
| `sync` | 1 | Registrar/Proxmox sync |

### Queue Monitoring

```bash
# Check queue size
php artisan queue:monitor

# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry {job-id}

# Clear failed jobs
php artisan queue:flush
```

---

## Scheduler

Cron entry (`crontab -u app -e`):

```cron
* * * * * php /var/www/hosting-platform/artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Commands

| Command | Schedule | Purpose |
|---|---|---|
| `billing:generate` | Daily 00:00 | Generate renewal invoices |
| `billing:reminder` | Daily 08:00 | Send payment reminders |
| `services:check-expiration` | Daily 00:30 | Check expired services |
| `services:suspend-expired` | Daily 01:00 | Suspend past-grace-period |
| `services:terminate-expired` | Daily 02:00 | Terminate past-suspension |
| `domains:sync` | Daily 03:00 | Sync domain status |
| `proxmox:sync` | Every 15 min | Sync node resource usage |
| `queue:prune-failed` | Daily 04:00 | Clean old failed jobs |
| `telescope:prune` | Daily 04:30 | Clean monitoring data |

---

## Maintenance Mode

```bash
# Enable maintenance mode
php artisan down --secret="maintenance-bypass-token" --retry=60

# Access during maintenance
# https://yourdomain.com/maintenance-bypass-token

# Disable maintenance mode
php artisan up
```

---

## Backup Strategy

### Database Backup (Daily)
```bash
# Add to crontab
0 3 * * * mysqldump -u hosting_user -p'strong_password_here' hosting_platform | gzip > /backup/db-$(date +\%Y\%m\%d).sql.gz

# Keep last 30 days
0 4 * * * find /backup/ -name "db-*.sql.gz" -mtime +30 -delete
```

### Application Files Backup
- Git repository is the source of truth
- Storage files (uploads): rsync to backup location
- `.env`: backup securely (contains credentials)

---

## Zero-Downtime Deployment

For future CI/CD pipeline:

```bash
# 1. Pull new code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
pnpm install && pnpm run build

# 3. Run migrations (backward-compatible)
php artisan migrate --force

# 4. Clear cache, re-optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Restart queue workers
sudo supervisorctl restart hosting-queue:*

# 6. Health check
curl -f https://yourdomain.com/api/health || echo "Deploy failed"
```

---

## Environment Checklist

- [ ] `.env` configured with production values
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Database credentials set
- [ ] Redis connected
- [ ] Queue connection = `redis`
- [ ] Mail configured
- [ ] All provider API keys set
- [ ] SSL certificate installed
- [ ] Supervisor running queue workers
- [ ] Cron scheduler active
- [ ] Storage permissions correct
- [ ] `storage:link` created
- [ ] Firewall: ports 80, 443 open; 3306, 6379 internal only
- [ ] Automatic security updates enabled: `sudo apt install unattended-upgrades`

---

## Quick Start (Development)

```bash
# Clone & setup
git clone <repo-url> && cd hosting-platform
cp .env.example .env
composer install
pnpm install

# Database
touch database/database.sqlite   # For local SQLite dev
php artisan migrate --seed

# Start development servers
composer run dev   # Starts Vite + Laravel

# Or separately:
php artisan serve          # Laravel on :8000
pnpm run dev               # Vite with HMR
php artisan queue:work     # Queue worker (separate terminal)
```
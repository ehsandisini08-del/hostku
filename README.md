# Platform Penjualan Hosting, Domain & VPS

Platform SaaS modern untuk penjualan domain, web hosting, dan VPS dengan website publik, customer dashboard, dan admin dashboard.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 + PHP 8.3+ |
| Frontend | Vue 3 + TypeScript + Inertia v3 |
| CSS | Tailwind CSS v4 |
| UI Components | Reka UI |
| Build | Vite |
| Database | MySQL / MariaDB |
| Cache & Queue | Redis |
| Auth | Laravel Fortify (2FA, Passkeys) |
| Testing | Pest PHP |
| Code Quality | Laravel Pint, PHPStan, Oxlint |

## Features

### Public Website
- Homepage dengan domain search
- Halaman produk (Hosting, VPS, Domain)
- Pricing comparison
- SEO optimized

### Customer Dashboard
- Overview layanan
- Manajemen VPS (start/stop/reboot/reinstall)
- Manajemen Hosting
- Manajemen Domain (renew, nameserver)
- Invoice & payment history
- Support ticket system
- 2FA & Passkey authentication

### Admin Dashboard
- Revenue & metrics dashboard
- Customer management
- Product & pricing management
- Order & invoice management
- Provisioning queue monitoring
- Server & Proxmox management
- Coupon & promo system
- Audit logging

### Integrations
- Payment: Midtrans, Xendit, DOKU (abstraction layer)
- VPS: Proxmox VE API
- Hosting: cPanel, DirectAdmin, Plesk (abstraction layer)
- Domain: Registrar API (abstraction layer)

## Requirements

- PHP 8.3+
- MySQL 8.0+ / MariaDB 10.11+
- Redis 7+
- Composer 2
- Node.js 20+ & pnpm
- Supervisor (production)

## Quick Start (Development)

```bash
# Clone repository
git clone <repository-url>
cd hosting-platform

# Setup environment
cp .env.example .env
php artisan key:generate

# Install dependencies
composer install
pnpm install

# Create SQLite database for development
touch database/database.sqlite

# Run migrations & seed
php artisan migrate --seed

# Start development
composer run dev
```

Application available at `http://localhost:8000`.

## Additional Commands

```bash
# Queue worker (needed for async tasks)
php artisan queue:work

# Frontend dev server only
pnpm run dev

# Run tests
php artisan test --compact

# Run code formatter
vendor/bin/pint

# Run static analysis
vendor/bin/phpstan analyse

# Generate TypeScript route helpers
php artisan wayfinder:generate
```

## Environment Variables

See `.env.example` for full list. Key variables:

```env
# Application
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hosting_platform
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=redis

# Proxmox
PROXMOX_HOST=https://proxmox.server.com:8006
PROXMOX_TOKEN_ID=root@pam!token
PROXMOX_TOKEN_SECRET=your-secret

# Payment Gateways
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx

XENDIT_API_KEY=xnd_development_xxxxx
XENDIT_CALLBACK_TOKEN=your-token

# Domain Registrar
DOMAIN_PROVIDER_API_URL=https://api.registrar.com
DOMAIN_PROVIDER_USERNAME=your-username
DOMAIN_PROVIDER_PASSWORD=your-password
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/     # Thin controllers
│   ├── Middleware/       # Auth, role, rate limiting
│   ├── Requests/         # Form request validation
│   └── Resources/        # API resources
├── Models/              # Eloquent models
├── Services/            # Business logic layer
│   ├── Billing/         # Invoice, tax, renewal
│   ├── Domain/          # Registrar integration
│   ├── Hosting/         # Panel integration
│   ├── Payment/         # Gateway adapters
│   ├── Proxmox/         # VPS management
│   └── Provisioning/    # Orchestration
├── Jobs/                # Queue jobs
├── Events/              # Domain events
├── Listeners/           # Event handlers
└── Policies/            # Authorization

resources/js/
├── pages/               # Inertia page components
├── components/          # Reusable Vue components
├── layouts/             # Page layouts
├── composables/         # Vue composables
├── stores/              # Pinia stores
└── types/               # TypeScript definitions
```

## Documentation

- [Architecture](docs/ARCHITECTURE.md) — System design & layers
- [Database](docs/DATABASE.md) — ERD & schema
- [API](docs/API.md) — Endpoints & contracts
- [Billing](docs/BILLING.md) — Billing cycles, invoices, coupons
- [Provisioning](docs/PROVISIONING.md) — VPS, Hosting, Domain flows
- [Security](docs/SECURITY.md) — Auth, authorization, secrets
- [Deployment](docs/DEPLOYMENT.md) — Server setup & deploy
- [Roadmap](docs/ROADMAP.md) — Implementation phases

## License

Proprietary. All rights reserved.# hostku

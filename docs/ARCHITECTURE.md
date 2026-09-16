# Architecture — Platform Hosting, Domain & VPS

## System Overview

Platform SaaS untuk penjualan Domain, Web Hosting, dan VPS dengan tiga interface utama:
- **Website Publik** — landing, produk, pricing, domain search
- **Customer Dashboard** — kelola layanan, invoice, ticket
- **Admin Dashboard** — kelola customer, produk, provisioning, billing

## High-Level Architecture

```
┌──────────────────────────────────────────────────────────┐
│                       CLIENT                              │
│  ┌─────────────┐  ┌──────────────────┐  ┌──────────────┐ │
│  │  Public Site  │  │ Customer Dashboard│  │ Admin Panel  │ │
│  │  (Inertia/Vue)│  │   (Inertia/Vue)   │  │(Inertia/Vue) │ │
│  └──────┬──────┘  └────────┬─────────┘  └──────┬───────┘ │
└─────────┼──────────────────┼───────────────────┼─────────┘
          │                  │                   │
          ▼                  ▼                   ▼
┌──────────────────────────────────────────────────────────┐
│                   INERTIA LAYER                           │
│         Server-side rendering + SPA hydration              │
│         Route: web.php (public) + api.php (API)           │
└──────────────────────────┬───────────────────────────────┘
                           │
          ┌────────────────┼────────────────┐
          ▼                ▼                ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  Controllers  │  │ Form Requests│  │ API Resources│
│  (thin layer) │  │ (validation) │  │ (serializer) │
└──────┬───────┘  └──────────────┘  └──────────────┘
       │
       ▼
┌──────────────────────────────────────────────────────────┐
│                   SERVICE LAYER                           │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌─────────────┐ │
│  │ Billing  │ │ Payment  │ │ Domain   │ │ Provisioning │ │
│  │ Service  │ │ Gateway  │ │ Provider │ │  Service     │ │
│  │          │ │ Interface│ │Interface │ │              │ │
│  └──────────┘ └──────────┘ └──────────┘ └─────────────┘ │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌─────────────┐ │
│  │ Proxmox  │ │ Hosting  │ │ Ticket   │ │ Notification │ │
│  │ Service  │ │ Provider │ │ Service  │ │  Service     │ │
│  │          │ │Interface │ │          │ │              │ │
│  └──────────┘ └──────────┘ └──────────┘ └─────────────┘ │
└──────────────────────────┬───────────────────────────────┘
                           │
          ┌────────────────┼────────────────┐
          ▼                ▼                ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│   Models      │  │   Events +   │  │   Policies   │
│  (Eloquent)   │  │   Listeners  │  │  (Gates)     │
└──────┬───────┘  └──────────────┘  └──────────────┘
       │
       ▼
┌──────────────────────────────────────────────────────────┐
│                   DATA LAYER                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────────────────┐   │
│  │  MySQL   │  │  Redis   │  │ External APIs        │   │
│  │ (primary)│  │ (cache/  │  │ • Proxmox VE API     │   │
│  │          │  │  queue)  │  │ • Midtrans/Xendit    │   │
│  └──────────┘  └──────────┘  │ • Domain Registrar    │   │
│                              │ • cPanel/DirectAdmin  │   │
│                              └──────────────────────┘   │
└──────────────────────────────────────────────────────────┘
```

## Layer Responsibilities

### Controllers (Thin)
- Menerima request, memanggil Service layer
- Tidak mengandung business logic
- Return Inertia response atau API Resource

### Service Classes
- Business logic utama
- Orchestration antar provider/interface
- Transaction management
- Dipanggil oleh Controllers, Jobs, dan Commands

### Provider Interfaces (Adapter Pattern)
- **PaymentGatewayInterface** — createPayment, verifyPayment, handleCallback, refund
- **DomainProviderInterface** — searchDomain, checkAvailability, register, renew, transfer
- **HostingProviderInterface** — createAccount, suspend, unsuspend, terminate, changePassword

```php
// app/Services/Payment/PaymentGatewayInterface.php
interface PaymentGatewayInterface
{
    public function createPayment(Invoice $invoice, array $options = []): PaymentResult;
    public function verifyPayment(string $transactionId): PaymentVerification;
    public function handleCallback(array $payload): CallbackResult;
    public function refund(Payment $payment, ?float $amount = null): RefundResult;
    public function getPaymentStatus(string $transactionId): string;
}
```

### Events & Listeners
| Event | Listener | Action |
|---|---|---|
| `PaymentReceived` | `ActivateService` | Activate service after payment |
| `PaymentReceived` | `SendPaymentConfirmation` | Email/notification |
| `ProvisioningCompleted` | `SendServiceActivated` | Notify customer |
| `ProvisioningFailed` | `NotifyAdminFailure` | Alert admin |
| `ServiceExpiring` | `SendExpirationReminder` | H-7, H-3, H-1 |
| `ServiceExpired` | `SuspendService` | Auto-suspend |
| `ServiceSuspended` | `ScheduleTermination` | Schedule termination job |

### Jobs (Queue)
| Job | Queue | Description |
|---|---|---|
| `ProvisionVpsJob` | `provisioning` | Create VM via Proxmox API |
| `ProvisionHostingJob` | `provisioning` | Create hosting account |
| `RegisterDomainJob` | `provisioning` | Register domain via registrar |
| `RenewDomainJob` | `provisioning` | Renew domain |
| `SendInvoiceEmailJob` | `mail` | Send invoice PDF |
| `SuspendServiceJob` | `maintenance` | Suspend expired service |
| `TerminateServiceJob` | `maintenance` | Terminate after grace period |
| `SyncDomainStatusJob` | `sync` | Sync domain status from registrar |

## Authentication & Authorization

### Guards
- **web** — session-based (public website + Inertia dashboard) via Fortify
- **sanctum** — token-based (API, future reseller/mobile)

### Roles
- **Customer** — manage own services, view own invoices/tickets
- **Admin** — full access to admin panel, manage all customers/services
- **Super Admin** — admin + manage admin users, system settings

### Authorization
- Laravel Policies untuk setiap model (ProductPolicy, OrderPolicy, VpsServicePolicy, dll)
- Middleware `role:admin` / `role:super-admin`
- API endpoint authorization di-check di backend, bukan hanya hide tombol

## Directory Structure

```
app/
├── Actions/                    # Single-action classes (Fortify, complex operations)
├── Console/Commands/           # Artisan commands (billing:generate, services:suspend-expired)
├── Enums/                      # PHP Enums (OrderStatus, ServiceStatus, BillingCycle)
├── Events/                     # Event classes
├── Exceptions/                 # Custom exceptions (ProvisioningFailedException, PaymentFailedException)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/              # Admin dashboard controllers
│   │   ├── Api/                # API controllers (v1)
│   │   ├── Auth/               # Auth controllers (if overriding Fortify)
│   │   └── Settings/           # User settings (existing)
│   ├── Middleware/              # Custom middleware (role, api.version)
│   ├── Requests/               # Form Request validation
│   └── Resources/              # API Resources (VpsResource, InvoiceResource, dll)
├── Interfaces/                  # Provider interfaces (PaymentGatewayInterface, dll)
├── Jobs/                        # Queue jobs
├── Listeners/                   # Event listeners
├── Models/                      # Eloquent models
├── Notifications/              # Laravel Notifications
├── Policies/                    # Authorization policies
├── Providers/                   # Service providers + adapter registrations
│   ├── AppServiceProvider.php
│   ├── FortifyServiceProvider.php
│   ├── PaymentServiceProvider.php
│   └── DomainServiceProvider.php
└── Services/
    ├── Billing/                 # Invoice generation, tax calculation, renewal
    ├── Domain/                  # Domain search, registration, sync
    ├── Hosting/                 # Hosting account management
    ├── Payment/                 # Payment gateway adapters
    │   ├── MidtransAdapter.php
    │   ├── XenditAdapter.php
    │   └── DokuAdapter.php
    ├── Proxmox/                 # Proxmox API client + VM management
    ├── Provisioning/            # Orchestration: order → provision
    └── Ticket/                  # Ticket management
```

## Key Design Decisions

1. **Adapter Pattern** untuk payment gateway, domain registrar, hosting panel, dan VPS provider — memungkinkan swap tanpa rewrite
2. **Queue-first** untuk semua provisioning — tidak ada synchronous call ke external API dari request cycle
3. **Event-driven** untuk lifecycle management — payment → provisioning, expiry → suspend, dsb
4. **Interface segregation** — setiap external service memiliki interface terpisah
5. **Scheduler** untuk recurring tasks — billing generation, reminder, suspension, sync
6. **API versioning** — `/api/v1/` prefix, siap untuk v2 di masa depan
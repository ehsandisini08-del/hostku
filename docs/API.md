# API Documentation

## Base URL

```
Production: https://yourdomain.com/api/v1
Development: http://localhost:8000/api/v1
```

## Authentication

### Session-based (Inertia SPA)
- Fortify handles login/register/2FA via session cookies
- CSRF token automatically included by Inertia
- Sanctum cookie-based SPA authentication

### API Token (Future: Reseller/Mobile)
- Header: `Authorization: Bearer {token}`
- Token generated via Sanctum personal access tokens

## Response Format

### Success
```json
{
    "data": { ... },
    "message": "Success message"
}
```

### Collection
```json
{
    "data": [ ... ],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 100
    }
}
```

### Error
```json
{
    "message": "Human readable error",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

---

## Public Endpoints

### Products

```
GET /api/v1/products
```
Query: `?type=hosting&billing_cycle=monthly&page=1`
Response: paginated product list with prices

```
GET /api/v1/products/{slug}
```
Response: single product detail with pricing options

### Domains

```
GET /api/v1/domains/search?q=example
```
Response: list of available TLDs with pricing
```json
{
    "data": {
        "domain": "example",
        "results": [
            {
                "tld": ".com",
                "available": true,
                "registration_price": 150000,
                "renewal_price": 150000,
                "currency": "IDR"
            },
            {
                "tld": ".id",
                "available": false,
                "registration_price": 100000,
                "renewal_price": 100000,
                "currency": "IDR"
            }
        ]
    }
}
```

```
GET /api/v1/domains/check?domain=example.com
```
Response:
```json
{
    "data": {
        "domain": "example.com",
        "available": true,
        "registration_price": 150000,
        "renewal_price": 150000,
        "transfer_price": 150000,
        "currency": "IDR"
    }
}
```

### TLD Pricing

```
GET /api/v1/domains/pricing
```
Response: all TLDs with registration, renewal, transfer prices

---

## Authenticated Endpoints (Customer)

*Header required: `Authorization: Bearer {token}` OR session cookie*

### Orders

```
GET /api/v1/orders
POST /api/v1/orders
GET /api/v1/orders/{id}
```

Create order:
```json
POST /api/v1/orders
{
    "items": [
        {
            "product_id": 1,
            "billing_cycle": "monthly",
            "quantity": 1,
            "meta": {
                "domain": "example.com",
                "hostname": "server1"
            }
        }
    ],
    "coupon_code": "WELCOME20"
}
```

### Invoices

```
GET /api/v1/invoices
GET /api/v1/invoices/{id}
GET /api/v1/invoices/{id}/download    (PDF)
```

### Payments

```
POST /api/v1/payments/create
```
```json
{
    "invoice_id": 42,
    "payment_method": "bank_transfer",
    "payment_channel": "bca"
}
```

```
GET /api/v1/payments
GET /api/v1/payments/{id}
GET /api/v1/payments/{id}/status
```

### Services

```
GET /api/v1/services
GET /api/v1/services/{id}
```

```
GET /api/v1/services?type=vps&status=active
```

### VPS Actions

```
POST /api/v1/vps/{id}/start
POST /api/v1/vps/{id}/stop
POST /api/v1/vps/{id}/shutdown
POST /api/v1/vps/{id}/reboot
POST /api/v1/vps/{id}/reinstall
```
Body (reinstall):
```json
{
    "os_template": "ubuntu-22.04",
    "confirmation": "REINSTALL"
}
```

```
GET  /api/v1/vps/{id}/status
GET  /api/v1/vps/{id}/usage       (CPU, RAM, Disk, Bandwidth)
GET  /api/v1/vps/{id}/snapshots
POST /api/v1/vps/{id}/snapshots
```

### Hosting Actions

```
GET  /api/v1/hosting/{id}
GET  /api/v1/hosting/{id}/usage
POST /api/v1/hosting/{id}/change-password
```

### Domain Actions

```
GET  /api/v1/domains/{id}
POST /api/v1/domains/{id}/renew
POST /api/v1/domains/{id}/transfer
PUT  /api/v1/domains/{id}/nameservers
GET  /api/v1/domains/{id}/epp
```

### Tickets

```
GET    /api/v1/tickets
POST   /api/v1/tickets
GET    /api/v1/tickets/{id}
POST   /api/v1/tickets/{id}/reply
PATCH  /api/v1/tickets/{id}/close
```

Create ticket:
```json
{
    "subject": "VPS tidak bisa diakses",
    "category": "technical",
    "priority": "high",
    "service_id": 15,
    "message": "Saya tidak bisa SSH ke VPS sejak pagi tadi...",
    "attachment": <file>
}
```

### Profile

```
GET    /api/v1/profile
PATCH  /api/v1/profile
```

### Notifications

```
GET    /api/v1/notifications
PATCH  /api/v1/notifications/{id}/read
POST   /api/v1/notifications/read-all
```

---

## Admin Endpoints

*All require `role:admin` middleware*

### Dashboard Stats

```
GET /api/v1/admin/dashboard
```
```json
{
    "data": {
        "revenue": {
            "total": 125000000,
            "this_month": 15000000
        },
        "counts": {
            "orders": 520,
            "active_customers": 180,
            "active_vps": 45,
            "active_hosting": 120,
            "domains": 210,
            "pending_invoices": 15,
            "failed_payments": 3
        },
        "provisioning_queue": {
            "queued": 3,
            "running": 1,
            "failed": 0
        }
    }
}
```

### Customer Management

```
GET    /api/v1/admin/customers
GET    /api/v1/admin/customers/{id}
PATCH  /api/v1/admin/customers/{id}
POST   /api/v1/admin/customers/{id}/suspend
POST   /api/v1/admin/customers/{id}/unsuspend
POST   /api/v1/admin/customers/{id}/reset-password
```

### Order Management

```
GET    /api/v1/admin/orders
GET    /api/v1/admin/orders/{id}
PATCH  /api/v1/admin/orders/{id}/status
```

### Product Management

```
GET    /api/v1/admin/products
POST   /api/v1/admin/products
PUT    /api/v1/admin/products/{id}
DELETE /api/v1/admin/products/{id}
```

Create product:
```json
{
    "type": "vps",
    "name": "VPS-1",
    "description": "1 CPU, 1GB RAM, 20GB SSD",
    "features": ["SSD Storage", "Full Root Access", "1 IPv4"],
    "is_active": true,
    "prices": [
        {"billing_cycle": "monthly", "price": 100000},
        {"billing_cycle": "annually", "price": 1000000}
    ],
    "vps_config": {
        "cpu_cores": 1,
        "ram_mb": 1024,
        "disk_mb": 20480,
        "os_templates": ["ubuntu-22.04", "debian-12"]
    }
}
```

### Service Management

```
GET    /api/v1/admin/services
GET    /api/v1/admin/services/{id}
PATCH  /api/v1/admin/services/{id}/status
```

### Domain Management

```
GET    /api/v1/admin/domains
GET    /api/v1/admin/domains/{id}
POST   /api/v1/admin/domains/sync
POST   /api/v1/admin/domains/{id}/renew
PUT    /api/v1/admin/domains/{id}/nameservers
```

### Invoice Management

```
GET    /api/v1/admin/invoices
GET    /api/v1/admin/invoices/{id}
POST   /api/v1/admin/invoices            (create manual invoice)
PATCH  /api/v1/admin/invoices/{id}/status
POST   /api/v1/admin/invoices/{id}/send
POST   /api/v1/admin/invoices/{id}/refund
```

### Payment Management

```
GET    /api/v1/admin/payments
GET    /api/v1/admin/payments/{id}
POST   /api/v1/admin/payments/verify/{id}
```

### Provisioning

```
GET    /api/v1/admin/provisioning
GET    /api/v1/admin/provisioning/{id}
POST   /api/v1/admin/provisioning/{id}/retry
```

### Server Management

```
GET    /api/v1/admin/servers
POST   /api/v1/admin/servers
PUT    /api/v1/admin/servers/{id}
DELETE /api/v1/admin/servers/{id}
POST   /api/v1/admin/servers/{id}/test-connection
```

### Proxmox Management

```
GET    /api/v1/admin/proxmox/servers
POST   /api/v1/admin/proxmox/servers
PUT    /api/v1/admin/proxmox/servers/{id}
DELETE /api/v1/admin/proxmox/servers/{id}

GET    /api/v1/admin/proxmox/servers/{id}/nodes
GET    /api/v1/admin/proxmox/servers/{id}/nodes/{node}/status
GET    /api/v1/admin/proxmox/servers/{id}/nodes/{node}/storage
GET    /api/v1/admin/proxmox/servers/{id}/nodes/{node}/templates

POST   /api/v1/admin/proxmox/sync
```

### Coupon Management

```
GET    /api/v1/admin/coupons
POST   /api/v1/admin/coupons
PUT    /api/v1/admin/coupons/{id}
DELETE /api/v1/admin/coupons/{id}
```

### Ticket Management

```
GET    /api/v1/admin/tickets
GET    /api/v1/admin/tickets/{id}
POST   /api/v1/admin/tickets/{id}/reply
PATCH  /api/v1/admin/tickets/{id}/status
```

### Audit Logs

```
GET    /api/v1/admin/audit-logs
```
Query: `?user_id=1&action=create&resource_type=vps_service&from=2025-01-01&to=2025-12-31`

### Settings

```
GET    /api/v1/admin/settings
PUT    /api/v1/admin/settings
```

### Admin Users (Super Admin only)

```
GET    /api/v1/admin/users
POST   /api/v1/admin/users
PUT    /api/v1/admin/users/{id}
DELETE /api/v1/admin/users/{id}
```

---

## Webhook Endpoints (Public — No Auth)

### Midtrans
```
POST /api/v1/webhooks/midtrans
```
Headers: signature verification via `X-Signature` or server key validation

### Xendit
```
POST /api/v1/webhooks/xendit
```
Headers: `x-callback-token` verification

### DOKU
```
POST /api/v1/webhooks/doku
```
Headers: signature verification

---

## Authorization Matrix

| Endpoint Prefix | Customer | Admin | Super Admin | Public |
|---|---|---|---|---|
| `/api/v1/products` | ✅ | ✅ | ✅ | ✅ |
| `/api/v1/domains/search` | ✅ | ✅ | ✅ | ✅ |
| `/api/v1/orders` | ✅ (own) | ✅ (all) | ✅ (all) | ❌ |
| `/api/v1/invoices` | ✅ (own) | ✅ (all) | ✅ (all) | ❌ |
| `/api/v1/payments` | ✅ (own) | ✅ (all) | ✅ (all) | ❌ |
| `/api/v1/services` | ✅ (own) | ✅ (all) | ✅ (all) | ❌ |
| `/api/v1/vps/*` | ✅ (own) | ✅ (all) | ✅ (all) | ❌ |
| `/api/v1/tickets` | ✅ (own) | ✅ (all) | ✅ (all) | ❌ |
| `/api/v1/admin/*` | ❌ | ✅ | ✅ | ❌ |
| `/api/v1/admin/users` | ❌ | ❌ | ✅ | ❌ |
| `/api/v1/webhooks/*` | ❌ | ❌ | ❌ | ✅ |

---

## Rate Limits

| Endpoint Group | Limit | Window |
|---|---|---|
| Public endpoints | 30 req | 1 min |
| Authenticated (customer) | 60 req | 1 min |
| Authenticated (admin) | 120 req | 1 min |
| Webhooks | 100 req | 1 min |
| Login attempts | 5 req | 1 min |
| VPS actions (start/stop/etc) | 10 req | 1 min |
| Domain search | 30 req | 1 min |
| Checkout | 10 req | 1 min |

---

## API Versioning

Current: `v1`

Version bump strategy:
- Backward-compatible additions → tetap v1
- Breaking changes → v2 dengan deprecation notice di v1
- v1 tetap didukung minimal 6 bulan setelah v2 rilis

## API Resources

Semua response data menggunakan Laravel API Resources:
```php
// app/Http/Resources/VpsServiceResource.php
class VpsServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hostname' => $this->hostname,
            'ip_address' => $this->ip_address,
            'status' => $this->vm_status,
            'cpu_cores' => $this->cpu_cores,
            'ram_mb' => $this->ram_mb,
            'disk_mb' => $this->disk_mb,
            'os_template' => $this->os_template,
            'created_at' => $this->created_at,
        ];
    }
}
```
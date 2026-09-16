# Roadmap — 10-Phase Implementation

## Phase 1 — Foundation ✅ DONE

**Status:** Complete (Laravel Starter Kit)

- [x] Project setup (Laravel 13.17 + Vue 3 + TypeScript + Vite + Tailwind CSS v4)
- [x] Database migration (users, cache, jobs, passkeys, 2FA)
- [x] Authentication (Fortify: login, register, forgot/reset password)
- [x] Email verification
- [x] Two-factor authentication (TOTP + recovery codes)
- [x] Passkeys / WebAuthn support
- [x] Role structure (User model)
- [x] Inertia SPA structure
- [x] Frontend layouts (sidebar, header, auth variants)
- [x] Settings pages (profile, security, appearance)
- [x] UI Component library (22 groups: Reka UI + Tailwind v4)
- [x] Theme/dark mode support

---

## Phase 2 — Public Website

**Target:** Website publik profesional dengan halaman produk

### Tasks
- [ ] Homepage (hero, domain search, featured products, plans, FAQ, CTA)
- [ ] Domain search page (live availability check via provider interface)
- [ ] Hosting plans page
- [ ] VPS plans page
- [ ] Pricing comparison page
- [ ] About page
- [ ] Contact page
- [ ] FAQ page
- [ ] Terms & Privacy Policy pages
- [ ] SEO (meta tags, OG, sitemap, robots.txt)
- [ ] Responsive mobile-first design

### Dependencies
- `Product` model + migration + seeder
- `DomainProviderInterface` + mock adapter

### Files Created
```
resources/js/pages/
├── Home.vue
├── Domain.vue
├── Hosting.vue
├── Vps.vue
├── Pricing.vue
├── About.vue
├── Contact.vue
├── Faq.vue
├── Terms.vue
└── Privacy.vue
```

---

## Phase 3 — Customer Dashboard

**Target:** Dashboard customer lengkap untuk kelola layanan

### Tasks
- [ ] Dashboard overview (total services, active, pending, unpaid invoices)
- [ ] Role: Customer (+ migration for roles table)
- [ ] My Services page (list all active/suspended services)
- [ ] Order history page
- [ ] Invoice list + detail
- [ ] Payment history
- [ ] Ticket system (create, view, reply)
- [ ] Notification center (in-app + email)
- [ ] Profile management (existing — enhance)

### Dependencies
- `Role` model + migration + seeder
- `Order` model
- `Invoice` model
- `Ticket` model
- `Notification` system
- `Service` base model (polymorphic: VPS/Hosting/Domain)

### Models Created
```
app/Models/
├── Role.php
├── Order.php
├── OrderItem.php
├── Invoice.php
├── InvoiceItem.php
├── Payment.php
├── Service.php (morph)
├── Ticket.php
├── TicketMessage.php
├── Notification.php
```

---

## Phase 4 — Admin Dashboard

**Target:** Admin panel lengkap untuk operasional

### Tasks
- [ ] Admin dashboard overview (revenue, orders, active services, pending)
- [ ] Customer management (list, search, filter, detail, suspend)
- [ ] Product management (domain TLDs, hosting plans, VPS plans)
- [ ] Order management (view, filter by status)
- [ ] Service management (per-type: VPS, hosting, domain)
- [ ] Invoice management
- [ ] Payment/transaction management
- [ ] Provisioning queue monitoring
- [ ] Server/infrastructure status
- [ ] Coupon management
- [ ] Settings (company info, currency, tax, invoice prefix, billing rules)
- [ ] Admin user management (Super Admin only)
- [ ] Audit log viewer

### Dependencies
- All models from Phase 3
- `Coupon` model
- `AuditLog` model
- Provider adapters registered

---

## Phase 5 — Payment Gateway

**Target:** Integrasi multi payment gateway dengan abstraction layer

### Tasks
- [ ] `PaymentGatewayInterface` contract
- [ ] `MidtransAdapter` implementation
- [ ] `XenditAdapter` implementation
- [ ] `DokuAdapter` implementation
- [ ] Checkout flow (pilih payment method → redirect/VA/QRIS)
- [ ] Webhook handler (verify signature, idempotency)
- [ ] Payment status reconciliation
- [ ] Refund flow
- [ ] `.env` configuration for each gateway
- [ ] Payment logging (raw payload + response)

### Key Files
```
app/Services/Payment/
├── PaymentGatewayInterface.php
├── MidtransAdapter.php
├── XenditAdapter.php
├── DokuAdapter.php
├── PaymentService.php
├── PaymentResult.php
└── PaymentVerification.php

app/Http/Controllers/Api/
└── WebhookController.php
```

### Idempotency Rules
- Gunakan `order_id` / `transaction_id` sebagai idempotency key
- Check `payments` table sebelum insert
- Semua webhook payload disimpan di `payment_transactions` table (raw)

---

## Phase 6 — Domain System

**Target:** Registrasi & manajemen domain via registrar API

### Tasks
- [ ] `DomainProviderInterface` contract
- [ ] Registrar adapter (ResellerCamp, IDWebhost, atau provider pilihan)
- [ ] TLD pricing management (admin)
- [ ] Domain search + availability check
- [ ] Domain registration flow (checkout → payment → register)
- [ ] Domain renewal flow
- [ ] Domain transfer flow
- [ ] Nameserver management
- [ ] Domain contact management
- [ ] WHOIS / domain info
- [ ] Sync domain status scheduler
- [ ] Domain expiration monitoring
- [ ] Registrar response logging

### Models
```
app/Models/
├── Domain.php
├── DomainContact.php
├── DomainPricing.php (per TLD)
└── DomainRegistration.php (registrar log)
```

### Key Files
```
app/Services/Domain/
├── DomainProviderInterface.php
├── ResellerCampAdapter.php
├── DomainService.php
└── DomainSyncService.php
```

---

## Phase 7 — Hosting System

**Target:** Manajemen paket hosting + provisioning ke panel

### Tasks
- [ ] Hosting plan management (admin: create, edit, enable/disable)
- [ ] Resource configuration (disk, websites, databases, emails, bandwidth)
- [ ] `HostingProviderInterface` contract
- [ ] cPanel adapter
- [ ] DirectAdmin adapter (future)
- [ ] Hosting provisioning (queue-based)
- [ ] Suspend / unsuspend hosting accounts
- [ ] Terminate hosting accounts
- [ ] Password reset / change
- [ ] Resource usage monitoring
- [ ] Server management (admin: add/edit servers)

### Models
```
app/Models/
├── HostingPlan.php
├── HostingService.php (extends Service)
├── HostingServer.php
└── HostingAccount.php (server-side account info)
```

---

## Phase 8 — VPS System (Proxmox)

**Target:** Provisioning VPS otomatis via Proxmox VE API

### Tasks
- [ ] VPS plan management (CPU, RAM, disk, bandwidth, OS templates)
- [ ] Proxmox server/node management (multi-server, multi-node)
- [ ] `ProxmoxService` class
- [ ] Test connection
- [ ] List nodes, storage, networks, templates
- [ ] Create VM (queue-based provisioning)
- [ ] Start / Stop / Shutdown / Reboot VM
- [ ] Delete / Clone VM
- [ ] Get VM status + resource usage
- [ ] Snapshot management
- [ ] Reinstall OS
- [ ] VNC console access
- [ ] VM resource resize (if supported)
- [ ] IP address assignment
- [ ] Node auto-selection (based on resource availability)

### Models
```
app/Models/
├── VpsPlan.php
├── VpsService.php (extends Service)
├── ProxmoxServer.php
├── ProxmoxNode.php
└── VmConfiguration.php
```

### Key Files
```
app/Services/Proxmox/
├── ProxmoxService.php
├── ProxmoxApiClient.php
├── NodeSelector.php
└── VmBuilder.php

app/Jobs/
└── ProvisionVpsJob.php
```

---

## Phase 9 — Automation

**Target:** Full automation untuk billing, renewal, suspension, notifications

### Tasks
- [ ] Billing scheduler (`billing:generate`) — generate invoice untuk service yang akan expired
- [ ] Renewal reminder scheduler (`billing:reminder`) — H-7, H-3, H-1
- [ ] Expiration checker (`services:check-expiration`)
- [ ] Auto-suspend (`services:suspend-expired`) — setelah grace period
- [ ] Auto-terminate (`services:terminate-expired`) — setelah masa suspend
- [ ] Domain sync scheduler (`domains:sync`)
- [ ] Proxmox sync scheduler (`proxmox:sync`)
- [ ] Notification system (email + WhatsApp jika tersedia)
- [ ] Invoice PDF generation
- [ ] Grace period configuration (admin settings)
- [ ] Suspension rules configuration

### Scheduler Commands
```
app/Console/Commands/
├── BillingGenerateCommand.php
├── BillingReminderCommand.php
├── ServicesCheckExpirationCommand.php
├── ServicesSuspendExpiredCommand.php
├── ServicesTerminateExpiredCommand.php
├── DomainsSyncCommand.php
└── ProxmoxSyncCommand.php
```

### Service Lifecycle State Machine
```
Pending → Active → Expiring (H-7) → Grace Period → Suspended → Terminated
                                    ↘ Paid → Active (renewed)
```

---

## Phase 10 — Hardening

**Target:** Production readiness

### Security
- [ ] Full authorization review (Policies on all models)
- [ ] Rate limiting on all API endpoints
- [ ] Input validation review
- [ ] CSRF protection verification
- [ ] XSS protection audit
- [ ] SQL injection audit
- [ ] File upload validation
- [ ] Credential encryption (provider API keys in DB)
- [ ] Webhook signature verification audit
- [ ] Session security review

### Testing
- [ ] Unit tests: billing calculation, coupon logic, service lifecycle
- [ ] Feature tests: registration, login, order, payment, provisioning
- [ ] Integration tests for all provider adapters (with mocks)
- [ ] Test coverage ≥ 80% on Service layer

### Performance
- [ ] Database index optimization
- [ ] Eager loading audit (N+1)
- [ ] Query optimization
- [ ] Redis caching strategy
- [ ] Frontend code splitting review
- [ ] Image optimization
- [ ] API pagination audit

### Logging & Monitoring
- [ ] Payment logging
- [ ] Provisioning logging
- [ ] Domain API logging
- [ ] Proxmox API logging
- [ ] Webhook logging
- [ ] Authentication logging
- [ ] Queue failure monitoring
- [ ] Admin dashboard: log viewer

### DevOps
- [ ] `.env.example` update (all provider keys)
- [ ] Deployment documentation
- [ ] Supervisor configuration
- [ ] Queue worker configuration
- [ ] Scheduler cron setup
- [ ] SSL (Let's Encrypt) setup guide
- [ ] Backup strategy

### Documentation
- [ ] README.md finalized
- [ ] API.md finalized
- [ ] DEPLOYMENT.md finalized
- [ ] Code-level PHPDoc for all Service classes

---

## Timeline Estimate

| Phase | Effort | Priority |
|---|---|---|
| Phase 1 — Foundation | ✅ Done | — |
| Phase 2 — Public Website | 1-2 weeks | HIGH |
| Phase 3 — Customer Dashboard | 2-3 weeks | HIGH |
| Phase 4 — Admin Dashboard | 2-3 weeks | HIGH |
| Phase 5 — Payment Gateway | 1-2 weeks | HIGH |
| Phase 6 — Domain System | 1-2 weeks | MEDIUM |
| Phase 7 — Hosting System | 1-2 weeks | MEDIUM |
| Phase 8 — VPS (Proxmox) | 2-3 weeks | MEDIUM |
| Phase 9 — Automation | 1-2 weeks | MEDIUM |
| Phase 10 — Hardening | 1-2 weeks | HIGH |
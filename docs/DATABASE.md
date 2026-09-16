# Database Design — ERD & Schema

## Entity Relationship Diagram

```
┌──────────┐     ┌──────────────┐     ┌───────────────┐
│   roles  │────<│    users     │────<│   customers   │
└──────────┘     └──────┬───────┘     └───────────────┘
                        │
        ┌───────────────┼───────────────────────┐
        │               │                       │
        ▼               ▼                       ▼
┌──────────────┐ ┌──────────────┐     ┌─────────────────┐
│    orders    │ │   tickets    │     │   passkeys      │
└──────┬───────┘ └──────┬───────┘     └─────────────────┘
       │                │
       ▼                ▼
┌──────────────┐ ┌───────────────┐
│ order_items  │ │ticket_messages│
└──────┬───────┘ └───────────────┘
       │
       ▼
┌──────────────┐      ┌──────────────┐
│  invoices    │────<│invoice_items │
└──────┬───────┘      └──────────────┘
       │
       ▼
┌──────────────┐
│   payments   │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│ payment_transactions │ (raw webhook logs)
└──────────────────────┘

┌──────────────┐
│  products    │ (polymorphic base)
└──────┬───────┘
       │
       ├── domain_pricing    (TLD prices)
       ├── hosting_plans     (shared hosting)
       └── vps_plans         (VPS)

┌──────────────────────────────────────┐
│              services                 │ (polymorphic)
└──────────────┬───────────────────────┘
       │
       ├── domains           (domain services)
       ├── hosting_services  (hosting accounts)
       └── vps_services      (VPS instances)

┌────────────────┐     ┌─────────────────┐
│proxmox_servers │────<│ proxmox_nodes   │
└────────────────┘     └─────────────────┘

┌──────────────┐     ┌──────────────┐
│   coupons    │────<│coupon_usages │
└──────────────┘     └──────────────┘

┌──────────────┐
│  audit_logs  │
└──────────────┘

┌──────────────┐     ┌──────────────┐
│  settings    │     │  webhook_logs│
└──────────────┘     └──────────────┘
```

## Core Tables

### users
```sql
CREATE TABLE users (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password    VARCHAR(255) NOT NULL,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    two_factor_confirmed_at TIMESTAMP NULL,
    role_id     BIGINT UNSIGNED NULL,
    remember_token VARCHAR(100) NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    deleted_at  TIMESTAMP NULL,

    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL,
    INDEX idx_users_email (email),
    INDEX idx_users_role (role_id)
);
```

### roles
```sql
CREATE TABLE roles (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,  -- 'customer', 'admin', 'super_admin'
    slug        VARCHAR(50) NOT NULL UNIQUE,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

### customers (extends users)
```sql
CREATE TABLE customers (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL UNIQUE,
    company     VARCHAR(255) NULL,
    phone       VARCHAR(30) NULL,
    address     TEXT NULL,
    city        VARCHAR(100) NULL,
    state       VARCHAR(100) NULL,
    country     VARCHAR(2) NULL DEFAULT 'ID',
    postal_code VARCHAR(20) NULL,
    tax_id      VARCHAR(50) NULL,       -- NPWP
    notes       TEXT NULL,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_customers_user (user_id)
);
```

## Product Tables

### products
```sql
CREATE TABLE products (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type            ENUM('domain', 'hosting', 'vps') NOT NULL,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL UNIQUE,
    description     TEXT NULL,
    features        JSON NULL,
    sort_order      INT UNSIGNED DEFAULT 0,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    INDEX idx_products_type (type),
    INDEX idx_products_slug (slug),
    INDEX idx_products_active (is_active)
);
```

### product_prices
```sql
CREATE TABLE product_prices (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT UNSIGNED NOT NULL,
    billing_cycle   ENUM('monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially') NOT NULL,
    price           DECIMAL(15, 2) NOT NULL,
    setup_fee       DECIMAL(15, 2) DEFAULT 0.00,
    is_promo        BOOLEAN NOT NULL DEFAULT FALSE,
    promo_price     DECIMAL(15, 2) NULL,
    promo_start     TIMESTAMP NULL,
    promo_end       TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_product_billing (product_id, billing_cycle),
    INDEX idx_prices_product (product_id)
);
```

### domain_pricing (extends products for TLDs)
```sql
CREATE TABLE domain_pricing (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT UNSIGNED NOT NULL UNIQUE,
    tld             VARCHAR(20) NOT NULL,
    registration_price  DECIMAL(15, 2) NOT NULL,
    renewal_price       DECIMAL(15, 2) NOT NULL,
    transfer_price      DECIMAL(15, 2) NOT NULL,
    min_years       INT DEFAULT 1,
    max_years       INT DEFAULT 10,
    is_premium      BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_domain_tld (tld)
);
```

### hosting_plans (extends products)
```sql
CREATE TABLE hosting_plans (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT UNSIGNED NOT NULL UNIQUE,
    disk_space_mb   INT UNSIGNED NOT NULL,
    bandwidth_mb    INT UNSIGNED NULL,
    max_websites    INT UNSIGNED DEFAULT 1,
    max_databases   INT UNSIGNED DEFAULT 1,
    max_emails      INT UNSIGNED DEFAULT 1,
    max_ftp         INT UNSIGNED DEFAULT 1,
    max_subdomains  INT UNSIGNED DEFAULT 0,
    server_type     VARCHAR(50) NULL,  -- 'cpanel', 'directadmin', 'plesk'
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### vps_plans (extends products)
```sql
CREATE TABLE vps_plans (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      BIGINT UNSIGNED NOT NULL UNIQUE,
    cpu_cores       INT UNSIGNED NOT NULL DEFAULT 1,
    ram_mb          INT UNSIGNED NOT NULL DEFAULT 1024,
    disk_mb         INT UNSIGNED NOT NULL DEFAULT 20480,
    bandwidth_mb    INT UNSIGNED NULL,
    ipv4_count      INT UNSIGNED DEFAULT 1,
    ipv6_count      INT UNSIGNED DEFAULT 0,
    os_templates    JSON NULL,     -- ['ubuntu-22.04', 'debian-12', 'centos-9']
    network_bridge  VARCHAR(50) DEFAULT 'vmbr0',
    storage_pool    VARCHAR(100) NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

## Service Tables (Polymorphic)

### services (base)
```sql
CREATE TABLE services (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    order_id        BIGINT UNSIGNED NOT NULL,
    serviceable_type VARCHAR(255) NOT NULL,  -- 'App\Models\Domain', 'App\Models\HostingService', 'App\Models\VpsService'
    serviceable_id  BIGINT UNSIGNED NOT NULL,
    status          ENUM('pending', 'active', 'expiring', 'grace_period', 'suspended', 'terminated', 'cancelled') NOT NULL DEFAULT 'pending',
    expired_at      DATE NULL,
    suspended_at    TIMESTAMP NULL,
    terminated_at   TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_services_user (user_id),
    INDEX idx_services_status (status),
    INDEX idx_services_morph (serviceable_type, serviceable_id),
    INDEX idx_services_expired (expired_at)
);
```

### domains (serviceable)
```sql
CREATE TABLE domains (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_name     VARCHAR(255) NOT NULL,
    tld             VARCHAR(20) NOT NULL,
    registrar       VARCHAR(100) NULL,
    registrar_id    VARCHAR(100) NULL,    -- ID dari registrar
    status          VARCHAR(50) NULL,     -- status dari registrar
    registration_date DATE NULL,
    expiration_date DATE NOT NULL,
    transfer_lock   BOOLEAN DEFAULT TRUE,
    auto_renew      BOOLEAN DEFAULT TRUE,
    nameservers     JSON NULL,            -- ['ns1.example.com', 'ns2.example.com']
    whois_privacy   BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    INDEX idx_domains_name (domain_name),
    INDEX idx_domains_expiration (expiration_date),
    INDEX idx_domains_tld (tld)
);

CREATE TABLE domain_contacts (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_id       BIGINT UNSIGNED NOT NULL,
    type            ENUM('registrant', 'admin', 'tech', 'billing') NOT NULL,
    name            VARCHAR(255) NOT NULL,
    organization    VARCHAR(255) NULL,
    email           VARCHAR(255) NOT NULL,
    phone           VARCHAR(30) NULL,
    address         TEXT NULL,
    city            VARCHAR(100) NULL,
    state           VARCHAR(100) NULL,
    country         VARCHAR(2) DEFAULT 'ID',
    postal_code     VARCHAR(20) NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE,
    INDEX idx_contacts_domain (domain_id)
);
```

### hosting_services (serviceable)
```sql
CREATE TABLE hosting_services (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hosting_plan_id BIGINT UNSIGNED NOT NULL,
    hosting_server_id BIGINT UNSIGNED NOT NULL,
    domain          VARCHAR(255) NULL,
    username        VARCHAR(100) NULL,    -- cPanel username
    server_ip       VARCHAR(45) NULL,
    panel_url       VARCHAR(255) NULL,
    provisioned_at  TIMESTAMP NULL,
    last_synced_at  TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (hosting_plan_id) REFERENCES hosting_plans(id),
    FOREIGN KEY (hosting_server_id) REFERENCES hosting_servers(id),
    INDEX idx_hosting_domain (domain)
);

CREATE TABLE hosting_servers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    hostname        VARCHAR(255) NOT NULL,
    ip_address      VARCHAR(45) NOT NULL,
    panel_type      VARCHAR(50) NOT NULL,  -- 'cpanel', 'directadmin', 'plesk'
    api_url         VARCHAR(255) NOT NULL,
    api_token       TEXT NOT NULL,          -- encrypted
    api_username    VARCHAR(255) NULL,
    max_accounts    INT UNSIGNED NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
```

### vps_services (serviceable)
```sql
CREATE TABLE vps_services (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vps_plan_id     BIGINT UNSIGNED NOT NULL,
    proxmox_node_id BIGINT UNSIGNED NULL,
    vm_id           INT UNSIGNED NULL,     -- Proxmox VMID
    hostname        VARCHAR(255) NULL,
    ip_address      VARCHAR(45) NULL,
    ipv6_address    VARCHAR(45) NULL,
    username        VARCHAR(100) DEFAULT 'root',
    os_template     VARCHAR(100) NULL,
    cpu_cores       INT UNSIGNED NOT NULL,
    ram_mb          INT UNSIGNED NOT NULL,
    disk_mb         INT UNSIGNED NOT NULL,
    bandwidth_mb    INT UNSIGNED NULL,
    vm_status       VARCHAR(50) DEFAULT 'stopped',  -- 'running', 'stopped', 'provisioning'
    provisioned_at  TIMESTAMP NULL,
    last_synced_at  TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (vps_plan_id) REFERENCES vps_plans(id),
    FOREIGN KEY (proxmox_node_id) REFERENCES proxmox_nodes(id),
    INDEX idx_vps_vmid (vm_id)
);
```

## Proxmox Infrastructure

### proxmox_servers
```sql
CREATE TABLE proxmox_servers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    host            VARCHAR(255) NOT NULL,
    port            INT UNSIGNED DEFAULT 8006,
    auth_type       ENUM('api_token', 'password') NOT NULL DEFAULT 'api_token',
    token_id        VARCHAR(255) NULL,
    token_secret    TEXT NULL,              -- encrypted
    username        VARCHAR(255) NULL,
    password        TEXT NULL,              -- encrypted (if auth_type=password)
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    last_checked_at TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
```

### proxmox_nodes
```sql
CREATE TABLE proxmox_nodes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proxmox_server_id BIGINT UNSIGNED NOT NULL,
    node_name       VARCHAR(255) NOT NULL,
    cpu_total       INT UNSIGNED DEFAULT 0,
    cpu_used        DECIMAL(5, 2) DEFAULT 0.00,
    ram_total_mb    BIGINT UNSIGNED DEFAULT 0,
    ram_used_mb     BIGINT UNSIGNED DEFAULT 0,
    disk_total_mb   BIGINT UNSIGNED DEFAULT 0,
    disk_used_mb    BIGINT UNSIGNED DEFAULT 0,
    is_online       BOOLEAN NOT NULL DEFAULT FALSE,
    last_synced_at  TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (proxmox_server_id) REFERENCES proxmox_servers(id) ON DELETE CASCADE,
    UNIQUE KEY uq_node_server (proxmox_server_id, node_name)
);
```

## Order & Billing Tables

### orders
```sql
CREATE TABLE orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    order_number    VARCHAR(50) NOT NULL UNIQUE,
    status          ENUM('pending', 'awaiting_payment', 'paid', 'processing', 'provisioning', 'active', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    subtotal        DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    tax_amount      DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    total           DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    coupon_id       BIGINT UNSIGNED NULL,
    notes           TEXT NULL,
    ip_address      VARCHAR(45) NULL,
    user_agent      TEXT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_number (order_number)
);
```

### order_items
```sql
CREATE TABLE order_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    product_id      BIGINT UNSIGNED NOT NULL,
    description     VARCHAR(255) NOT NULL,
    billing_cycle   ENUM('monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially', 'onetime') NOT NULL,
    quantity        INT UNSIGNED DEFAULT 1,
    unit_price      DECIMAL(15, 2) NOT NULL,
    setup_fee       DECIMAL(15, 2) DEFAULT 0.00,
    total           DECIMAL(15, 2) NOT NULL,
    meta            JSON NULL,     -- domain name, hostname, config options
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_items_order (order_id)
);
```

### invoices
```sql
CREATE TABLE invoices (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    order_id        BIGINT UNSIGNED NULL,
    invoice_number  VARCHAR(50) NOT NULL UNIQUE,
    status          ENUM('draft', 'pending', 'paid', 'expired', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    subtotal        DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    tax_amount      DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    total           DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    due_date        DATE NOT NULL,
    paid_at         TIMESTAMP NULL,
    payment_method  VARCHAR(50) NULL,
    notes           TEXT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_invoices_user (user_id),
    INDEX idx_invoices_status (status),
    INDEX idx_invoices_due (due_date),
    INDEX idx_invoices_number (invoice_number)
);
```

### invoice_items
```sql
CREATE TABLE invoice_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id      BIGINT UNSIGNED NOT NULL,
    service_id      BIGINT UNSIGNED NULL,   -- link ke service yang ditagih
    description     VARCHAR(255) NOT NULL,
    quantity        INT UNSIGNED DEFAULT 1,
    unit_price      DECIMAL(15, 2) NOT NULL,
    total           DECIMAL(15, 2) NOT NULL,
    period_start    DATE NULL,
    period_end      DATE NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    INDEX idx_items_invoice (invoice_id)
);
```

### payments
```sql
CREATE TABLE payments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id      BIGINT UNSIGNED NOT NULL,
    transaction_id  VARCHAR(100) NOT NULL UNIQUE,  -- dari payment gateway
    gateway         VARCHAR(50) NOT NULL,     -- 'midtrans', 'xendit', 'doku'
    amount          DECIMAL(15, 2) NOT NULL,
    status          ENUM('pending', 'paid', 'failed', 'expired', 'refunded') NOT NULL DEFAULT 'pending',
    payment_method  VARCHAR(50) NULL,         -- 'bank_transfer', 'credit_card', 'qris', 'va'
    payment_channel VARCHAR(50) NULL,         -- 'bca', 'bni', 'gopay'
    paid_at         TIMESTAMP NULL,
    raw_response    JSON NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX idx_payments_invoice (invoice_id),
    INDEX idx_payments_transaction (transaction_id),
    INDEX idx_payments_status (status)
);
```

### payment_transactions (webhook logs)
```sql
CREATE TABLE payment_transactions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id  VARCHAR(100) NOT NULL,
    gateway         VARCHAR(50) NOT NULL,
    event_type      VARCHAR(50) NOT NULL,     -- 'payment.success', 'payment.failed'
    raw_payload     JSON NOT NULL,
    processed       BOOLEAN NOT NULL DEFAULT FALSE,
    processed_at    TIMESTAMP NULL,
    error_message   TEXT NULL,
    created_at      TIMESTAMP NULL,

    INDEX idx_pt_transaction (transaction_id),
    INDEX idx_pt_gateway (gateway),
    INDEX idx_pt_processed (processed)
);
```

## Supporting Tables

### coupons
```sql
CREATE TABLE coupons (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    type            ENUM('percentage', 'fixed') NOT NULL,
    value           DECIMAL(15, 2) NOT NULL,
    min_order       DECIMAL(15, 2) DEFAULT 0.00,
    max_usage       INT UNSIGNED NULL,
    per_user_limit  INT UNSIGNED DEFAULT 1,
    starts_at       TIMESTAMP NULL,
    expires_at      TIMESTAMP NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    product_ids     JSON NULL,      -- null = all products
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    INDEX idx_coupons_code (code),
    INDEX idx_coupons_active (is_active, expires_at)
);

CREATE TABLE coupon_usages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id       BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    order_id        BIGINT UNSIGNED NOT NULL,
    discount_amount DECIMAL(15, 2) NOT NULL,
    created_at      TIMESTAMP NULL,

    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_usages_coupon (coupon_id),
    INDEX idx_usages_user (user_id)
);
```

### tickets
```sql
CREATE TABLE tickets (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    subject         VARCHAR(255) NOT NULL,
    category        VARCHAR(50) NOT NULL DEFAULT 'general',
    priority        ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    status          ENUM('open', 'pending', 'answered', 'closed') NOT NULL DEFAULT 'open',
    service_id      BIGINT UNSIGNED NULL,
    last_reply_at   TIMESTAMP NULL,
    closed_at       TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    INDEX idx_tickets_user (user_id),
    INDEX idx_tickets_status (status),
    INDEX idx_tickets_category (category)
);

CREATE TABLE ticket_messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id       BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL, -- sender
    message         TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    created_at      TIMESTAMP NULL,

    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_ticket (ticket_id)
);
```

### notifications
```sql
CREATE TABLE notifications (
    id              CHAR(36) PRIMARY KEY,     -- UUID
    type            VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id   BIGINT UNSIGNED NOT NULL,
    data            JSON NOT NULL,
    read_at         TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    INDEX idx_notifications_notifiable (notifiable_type, notifiable_id),
    INDEX idx_notifications_read (read_at)
);
```

### audit_logs
```sql
CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NULL,
    action          VARCHAR(100) NOT NULL,
    resource_type   VARCHAR(100) NOT NULL,
    resource_id     BIGINT UNSIGNED NULL,
    ip_address      VARCHAR(45) NULL,
    user_agent      TEXT NULL,
    metadata        JSON NULL,
    created_at      TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_resource (resource_type, resource_id),
    INDEX idx_audit_created (created_at)
);
```

### settings
```sql
CREATE TABLE settings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key             VARCHAR(100) NOT NULL UNIQUE,
    value           TEXT NULL,
    type            VARCHAR(50) DEFAULT 'string',  -- 'string', 'json', 'boolean', 'integer'
    group           VARCHAR(50) DEFAULT 'general',
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    INDEX idx_settings_group (`group`)
);
```

### webhook_logs
```sql
CREATE TABLE webhook_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider        VARCHAR(50) NOT NULL,
    event_type      VARCHAR(100) NULL,
    payload         JSON NOT NULL,
    processed       BOOLEAN NOT NULL DEFAULT FALSE,
    error           TEXT NULL,
    created_at      TIMESTAMP NULL,

    INDEX idx_webhook_provider (provider),
    INDEX idx_webhook_processed (processed)
);
```

### provisioning_jobs
```sql
CREATE TABLE provisioning_jobs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_type    VARCHAR(100) NOT NULL,
    service_id      BIGINT UNSIGNED NOT NULL,
    action          VARCHAR(50) NOT NULL,  -- 'create', 'suspend', 'unsuspend', 'terminate'
    status          ENUM('queued', 'running', 'completed', 'failed') NOT NULL DEFAULT 'queued',
    attempts        INT UNSIGNED DEFAULT 0,
    max_attempts    INT UNSIGNED DEFAULT 3,
    started_at      TIMESTAMP NULL,
    completed_at    TIMESTAMP NULL,
    error_message   TEXT NULL,
    payload         JSON NULL,
    result          JSON NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    INDEX idx_pj_service (service_type, service_id),
    INDEX idx_pj_status (status),
    INDEX idx_pj_created (created_at)
);
```

## Indexes Strategy

| Table | Index | Purpose |
|---|---|---|
| users | email, role_id | Login lookup, role filtering |
| orders | user_id, status, order_number | Customer orders, status filter, lookup |
| invoices | user_id, status, due_date, invoice_number | Customer invoices, billing runs |
| payments | invoice_id, transaction_id | Idempotency, lookup |
| services | user_id, status, expired_at, morph | Dashboard, billing scheduler |
| domains | domain_name, expiration_date, tld | Search, renewal checks |
| vps_services | vm_id, proxmox_node_id | Proxmox sync |
| audit_logs | user_id, resource_type+resource_id | Audit trail |
| tickets | user_id, status, category | Support queue |

## Soft Deletes

Tables with `deleted_at` (SoftDeletes trait):
- **users** — preserve order history meski user dihapus
- **orders** — audit trail
- **invoices** — financial record integrity

## Notes

- Semua monetary values: `DECIMAL(15, 2)` untuk presisi
- Semua foreign key: constraint + index
- JSON columns: hanya untuk data semi-structured (features, meta, nameservers)
- Timestamps: `created_at` + `updated_at` di semua tabel
- Encrypted fields: `token_secret`, `password` (provider) — gunakan Laravel encryption
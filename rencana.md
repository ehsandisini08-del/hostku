# PROMPT AI --- Rencana Pembangunan Platform Penjualan Hosting, Domain & VPS

## ROLE

Anda adalah **Senior Software Architect, Full-Stack Developer, DevOps
Engineer, dan UI/UX Designer** yang bertugas merancang dan membangun
platform SaaS untuk penjualan:

-   Domain
-   Web Hosting
-   VPS
-   Produk digital/server lainnya di masa depan

Platform harus memiliki **website publik, customer dashboard, dan admin
dashboard**, serta mampu melakukan otomatisasi order, pembayaran,
provisioning, billing, suspend, dan pengelolaan layanan.

Jangan hanya membuat prototype tampilan. Rancang sistem yang
**production-ready, aman, scalable, modular, mudah dirawat, dan siap
dikembangkan**.

------------------------------------------------------------------------

# 1. TUJUAN UTAMA

Bangun sebuah platform hosting provider modern dengan alur:

``` text
Pengunjung
   ↓
Website
   ↓
Pilih Produk
   ↓
Checkout
   ↓
Register / Login
   ↓
Order
   ↓
Invoice
   ↓
Payment Gateway
   ↓
Payment Berhasil
   ↓
Provisioning Otomatis
   ↓
Service Aktif
   ↓
Customer Dashboard
```

Untuk VPS:

``` text
Customer Order VPS
       ↓
Payment Berhasil
       ↓
Backend
       ↓
Proxmox API
       ↓
Pilih Node
       ↓
Create VM
       ↓
Set CPU / RAM / Disk / Network
       ↓
Set Password / SSH
       ↓
Start VM
       ↓
Simpan Informasi VPS
       ↓
Customer dapat mengelola VPS
```

------------------------------------------------------------------------

# 2. STACK TEKNOLOGI

Gunakan stack berikut kecuali ada alasan teknis yang kuat untuk
menggantinya.

## Frontend

-   Vue 3
-   TypeScript
-   Vite
-   Tailwind CSS
-   Vue Router
-   Pinia
-   Axios
-   Lucide Icons

Gunakan desain modern, responsive, mobile-first, dan profesional.

## Backend

-   Laravel
-   PHP versi yang kompatibel dengan Laravel yang digunakan
-   Laravel API
-   Laravel Sanctum
-   Laravel Queue
-   Laravel Scheduler
-   Laravel Events
-   Laravel Notifications

## Database

Gunakan:

-   MySQL / MariaDB

Pastikan database dirancang dengan:

-   foreign key
-   index
-   unique constraint
-   soft delete jika diperlukan
-   audit fields
-   timestamps

## Cache / Queue

Gunakan:

-   Redis

## Server

-   Ubuntu Server
-   Nginx
-   PHP-FPM
-   Supervisor
-   SSL Let's Encrypt

## VPS

Integrasikan:

-   Proxmox VE API

## Payment

Buat abstraction layer agar dapat menggunakan beberapa payment gateway:

-   Midtrans
-   Xendit
-   DOKU

Jangan mengikat business logic langsung ke satu payment gateway.

## Domain

Buat abstraction layer untuk Registrar API.

Contoh:

``` text
DomainProviderInterface
├── searchDomain()
├── checkAvailability()
├── registerDomain()
├── renewDomain()
├── transferDomain()
├── getDomainInfo()
├── updateNameserver()
└── getTldPricing()
```

Dengan begitu registrar dapat diganti tanpa mengubah seluruh sistem.

------------------------------------------------------------------------

# 3. ARSITEKTUR SISTEM

Gunakan arsitektur modular.

Minimal pisahkan:

``` text
Frontend
    ↓
REST API
    ↓
Application / Service Layer
    ↓
Repositories / Providers
    ↓
Database / External API
```

Jangan menaruh semua logic di Controller.

Gunakan:

-   Form Request
-   API Resource
-   Service Class
-   Action Class jika diperlukan
-   Jobs
-   Events
-   Listeners
-   Policies
-   Middleware
-   Interfaces
-   Provider adapters

------------------------------------------------------------------------

# 4. STRUKTUR PROYEK

Rencanakan struktur seperti:

``` text
project/
├── app/
│   ├── Actions/
│   ├── Console/
│   ├── Events/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/
│   ├── Listeners/
│   ├── Models/
│   ├── Notifications/
│   ├── Policies/
│   ├── Services/
│   │   ├── Billing/
│   │   ├── Domain/
│   │   ├── Hosting/
│   │   ├── Payment/
│   │   ├── Proxmox/
│   │   └── Provisioning/
│   └── Providers/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   └── frontend/
│       ├── components/
│       ├── layouts/
│       ├── pages/
│       ├── stores/
│       ├── services/
│       └── types/
├── routes/
│   ├── api.php
│   └── web.php
└── tests/
```

Struktur boleh disesuaikan jika ada alasan yang lebih baik.

------------------------------------------------------------------------

# 5. WEBSITE PUBLIK

Buat website publik profesional.

Halaman minimal:

``` text
/
├── Home
├── Domain
├── Hosting
├── VPS
├── Pricing
├── About
├── Contact
├── FAQ
├── Login
├── Register
├── Checkout
├── Terms
└── Privacy Policy
```

## Homepage

Harus memiliki:

-   Hero section
-   Search domain
-   Produk unggulan
-   Hosting plans
-   VPS plans
-   Feature comparison
-   Why choose us
-   FAQ
-   CTA
-   Footer

Jangan membuat desain terlalu penuh dengan card.

Gunakan layout modern seperti website SaaS/hosting provider profesional.

------------------------------------------------------------------------

# 6. DOMAIN SYSTEM

Fitur domain:

### Search

Customer dapat:

``` text
example.com
example.id
example.net
example.org
```

dan sistem memeriksa availability melalui provider.

### Pricing

Admin dapat mengatur:

-   Registration price
-   Renewal price
-   Transfer price
-   Promo price
-   TLD

Contoh:

``` text
.com
.id
.net
.org
.co.id
.my.id
```

Harga jangan hard-code.

### Domain Management

Customer dapat:

-   melihat domain
-   melihat expiration date
-   renew
-   transfer
-   update nameserver
-   melihat status
-   melihat invoice
-   mengelola contact/domain information sesuai kemampuan registrar

Admin dapat:

-   melihat semua domain
-   melakukan sinkronisasi
-   mengubah nameserver
-   melihat expiration
-   melakukan renew
-   melihat registrar response/log

------------------------------------------------------------------------

# 7. HOSTING SYSTEM

Buat sistem paket hosting.

Contoh:

``` text
Starter
- 5 GB SSD
- 1 Website
- 1 Database
- 1 Email

Business
- 20 GB SSD
- 5 Website
- 5 Database
- 10 Email

Pro
- 50 GB SSD
- Unlimited Website
- Unlimited Database
```

Semua spesifikasi harus configurable.

Admin dapat:

-   membuat paket
-   edit paket
-   mengaktifkan/nonaktifkan paket
-   menentukan harga
-   menentukan billing cycle
-   menentukan resource
-   menentukan server/provider

Siapkan abstraction untuk provisioning hosting agar dapat dikembangkan
ke:

-   cPanel
-   Plesk
-   DirectAdmin
-   custom hosting server

------------------------------------------------------------------------

# 8. VPS SYSTEM

Admin dapat membuat VPS plans.

Contoh:

``` text
VPS-1
1 CPU
1 GB RAM
20 GB SSD

VPS-2
2 CPU
2 GB RAM
40 GB SSD

VPS-3
4 CPU
4 GB RAM
80 GB SSD
```

Admin dapat menentukan:

-   CPU
-   RAM
-   Disk
-   Bandwidth
-   IPv4
-   IPv6
-   OS
-   Price
-   Billing cycle
-   Proxmox node
-   Storage
-   Network bridge

------------------------------------------------------------------------

# 9. PROXMOX INTEGRATION

Buat modul:

``` text
ProxmoxService
```

Kemampuan minimal:

-   Test connection
-   List nodes
-   List storage
-   List networks
-   List templates
-   Create VM
-   Start VM
-   Stop VM
-   Shutdown VM
-   Reboot VM
-   Delete VM
-   Clone VM
-   Get VM status
-   Get resource usage
-   Get IP information jika tersedia
-   Snapshot
-   Resize resource jika didukung

Gunakan queue untuk proses provisioning.

Jangan membuat request provisioning panjang secara synchronous jika
proses dapat memakan waktu.

Contoh:

``` text
Payment Success
      ↓
CreateProvisioningJob
      ↓
Queue
      ↓
Proxmox API
      ↓
Create VM
      ↓
Configure VM
      ↓
Start VM
      ↓
Update Service
```

------------------------------------------------------------------------

# 10. CUSTOMER DASHBOARD

Dashboard customer harus memiliki:

``` text
Dashboard
├── Overview
├── My Services
├── VPS
├── Hosting
├── Domains
├── Orders
├── Invoices
├── Payments
├── Tickets
├── Notifications
├── Profile
├── Security
└── Logout
```

## Dashboard Overview

Tampilkan:

-   Total services
-   Active services
-   Pending orders
-   Unpaid invoices
-   Domain expiration
-   VPS status
-   Recent transactions
-   Notifications

------------------------------------------------------------------------

# 11. VPS CUSTOMER MANAGEMENT

Customer dapat:

-   melihat VPS
-   melihat status
-   start
-   stop
-   reboot
-   shutdown
-   reinstall
-   melihat IP
-   melihat username
-   melihat resource
-   melihat OS
-   melihat bandwidth
-   melihat penggunaan CPU
-   melihat RAM
-   melihat disk

Aksi yang berisiko harus memiliki confirmation.

Contoh:

``` text
Reinstall VPS?
Semua data pada VPS akan terhapus.
[Cancel] [Reinstall]
```

------------------------------------------------------------------------

# 12. BILLING SYSTEM

Sistem billing harus mendukung:

-   Monthly
-   Quarterly
-   Semi-annually
-   Annually
-   Custom billing cycle

Invoice memiliki:

``` text
Invoice Number
Customer
Items
Subtotal
Discount
Tax
Total
Due Date
Status
Payment Method
Paid At
```

Status:

``` text
Draft
Pending
Paid
Expired
Cancelled
Refunded
```

------------------------------------------------------------------------

# 13. RECURRING BILLING

Buat sistem renewal otomatis.

Contoh:

``` text
Service expires:
30 September

Reminder:
H-7
H-3
H-1

Due:
30 September

Jika belum dibayar:
Grace Period

Setelah grace period:
Suspend

Setelah masa suspend:
Terminate
```

Semua aturan harus configurable oleh admin.

Jangan hard-code tanggal atau durasi.

------------------------------------------------------------------------

# 14. PAYMENT GATEWAY

Buat interface:

``` text
PaymentGatewayInterface
```

Minimal:

``` text
createPayment()
verifyPayment()
handleCallback()
refund()
getPaymentStatus()
```

Webhook harus:

-   verify signature
-   idempotent
-   mencatat raw payload
-   mencatat response
-   tidak memproses pembayaran dua kali

Contoh flow:

``` text
Customer Pay
     ↓
Gateway
     ↓
Webhook
     ↓
Verify Signature
     ↓
Check Idempotency
     ↓
Mark Invoice Paid
     ↓
Activate / Provision Service
```

------------------------------------------------------------------------

# 15. ORDER SYSTEM

Order harus memiliki lifecycle.

Contoh:

``` text
Pending
↓
Awaiting Payment
↓
Paid
↓
Processing
↓
Provisioning
↓
Active
```

Jika gagal:

``` text
Provisioning Failed
```

Admin dapat retry provisioning.

Jangan membuat service langsung aktif hanya karena customer membuka
halaman pembayaran.

Status harus berdasarkan webhook/payment verification.

------------------------------------------------------------------------

# 16. ADMIN DASHBOARD

Admin dashboard:

``` text
Admin
├── Dashboard
├── Customers
├── Orders
├── Services
├── Domains
├── Hosting
├── VPS
├── Products
├── Pricing
├── Invoices
├── Payments
├── Transactions
├── Provisioning
├── Servers
├── Proxmox
├── Tickets
├── Coupons
├── Notifications
├── Settings
├── Logs
└── Admin Users
```

------------------------------------------------------------------------

# 17. ADMIN DASHBOARD OVERVIEW

Tampilkan:

-   Revenue
-   Revenue bulan ini
-   Orders
-   Active customers
-   Active VPS
-   Active hosting
-   Registered domains
-   Pending invoices
-   Failed payments
-   Provisioning queue
-   Server status

Gunakan grafik hanya jika membantu.

Jangan membuat dashboard penuh grafik yang tidak berguna.

------------------------------------------------------------------------

# 18. CUSTOMER MANAGEMENT

Admin dapat:

-   melihat customer
-   search
-   filter
-   melihat detail
-   melihat orders
-   melihat services
-   melihat invoices
-   melihat payments
-   suspend customer jika diperlukan
-   reset password melalui mekanisme aman
-   melihat activity/log

Jangan menampilkan password customer.

------------------------------------------------------------------------

# 19. PRODUCT MANAGEMENT

Admin dapat membuat produk:

``` text
Product
├── Domain
├── Hosting
└── VPS
```

Setiap produk memiliki:

``` text
Name
Slug
Description
Price
Billing Cycle
Status
Features
Provisioning Configuration
Sort Order
```

Gunakan database, bukan hard-code.

------------------------------------------------------------------------

# 20. COUPON / PROMO

Buat sistem coupon:

-   Percentage discount
-   Fixed discount
-   Expiration
-   Minimum order
-   Maximum usage
-   Per customer limit
-   Product restriction

Contoh:

``` text
WELCOME20
20% OFF
Maximum 1 use/customer
Expires: configurable
```

------------------------------------------------------------------------

# 21. TICKETING

Customer dapat membuat ticket:

``` text
Subject
Category
Priority
Message
Attachment
```

Status:

``` text
Open
Pending
Answered
Closed
```

Admin dapat membalas ticket.

Simpan seluruh conversation.

------------------------------------------------------------------------

# 22. NOTIFICATION SYSTEM

Gunakan Laravel Notifications.

Channel:

-   Database
-   Email
-   WhatsApp jika provider tersedia

Notifikasi:

-   Invoice dibuat
-   Payment berhasil
-   Payment gagal
-   VPS berhasil dibuat
-   VPS gagal dibuat
-   Service aktif
-   Service akan expired
-   Domain akan expired
-   Service suspended
-   Ticket reply

------------------------------------------------------------------------

# 23. AUTHENTICATION & AUTHORIZATION

Role minimal:

``` text
Customer
Admin
Super Admin
```

Gunakan:

-   Laravel Sanctum
-   Policies
-   Gates
-   Middleware

Admin dan customer tidak boleh saling mengakses endpoint.

Pastikan authorization diperiksa di backend, bukan hanya menyembunyikan
tombol frontend.

------------------------------------------------------------------------

# 24. SECURITY

Prioritaskan:

-   SQL injection protection
-   XSS protection
-   CSRF protection
-   Rate limiting
-   Secure password hashing
-   API authentication
-   Authorization
-   Webhook signature verification
-   Input validation
-   File upload validation
-   Audit log
-   Secure secrets
-   Encryption untuk credential provider jika diperlukan
-   Jangan menyimpan API key di frontend
-   Jangan menyimpan password VPS secara plaintext jika tidak diperlukan

Credential Proxmox/payment/domain provider hanya boleh berada di
backend.

------------------------------------------------------------------------

# 25. AUDIT LOG

Catat aktivitas penting:

``` text
User
Action
Resource
Resource ID
IP
User Agent
Timestamp
Metadata
```

Contoh:

``` text
Admin
CREATE
VPS Service
#1024
IP: x.x.x.x
```

------------------------------------------------------------------------

# 26. ERROR HANDLING

Semua external API harus memiliki:

-   timeout
-   retry
-   logging
-   graceful failure
-   error mapping
-   queue retry
-   idempotency

Jangan menampilkan error teknis mentah kepada customer.

Contoh:

Jangan:

``` text
cURL error 28...
```

Tampilkan:

``` text
Layanan sedang mengalami gangguan sementara.
Silakan coba kembali beberapa saat lagi.
```

Detail error tetap masuk log untuk admin.

------------------------------------------------------------------------

# 27. DATABASE

Rancang ERD sebelum coding.

Minimal tabel:

``` text
users
roles
permissions
customers
products
product_prices
domains
domain_contacts
hosting_services
vps_services
proxmox_servers
orders
order_items
invoices
invoice_items
payments
payment_transactions
provisioning_jobs
tickets
ticket_messages
coupons
coupon_usages
notifications
audit_logs
settings
```

Sesuaikan tabel jika arsitektur membutuhkan perubahan.

Pastikan relasi jelas.

------------------------------------------------------------------------

# 28. API

Gunakan REST API yang konsisten.

Contoh:

``` text
/api/v1/auth/login
/api/v1/auth/register

/api/v1/products
/api/v1/domains/search
/api/v1/domains/check

/api/v1/orders
/api/v1/invoices
/api/v1/payments

/api/v1/services
/api/v1/vps
/api/v1/hosting

/api/v1/tickets
```

Admin:

``` text
/api/v1/admin/customers
/api/v1/admin/orders
/api/v1/admin/products
/api/v1/admin/services
/api/v1/admin/vps
/api/v1/admin/domains
/api/v1/admin/payments
/api/v1/admin/provisioning
/api/v1/admin/settings
```

------------------------------------------------------------------------

# 29. API DOCUMENTATION

Dokumentasikan API.

Setiap endpoint minimal memiliki:

-   Method
-   URL
-   Authentication
-   Parameters
-   Request body
-   Response
-   Error response
-   Authorization requirement

Gunakan OpenAPI/Swagger jika sesuai.

------------------------------------------------------------------------

# 30. FRONTEND DESIGN

Gunakan desain:

-   Modern
-   Professional
-   Clean
-   Responsive
-   Mobile friendly
-   Fast
-   Accessible

Hindari:

-   gradient berlebihan
-   card berlebihan
-   animasi berlebihan
-   dashboard terlalu ramai
-   tombol yang tidak jelas
-   layout zig-zag yang tidak perlu

Gunakan:

-   whitespace
-   typography hierarchy
-   table yang nyaman
-   filter
-   search
-   tabs
-   drawer
-   modal
-   status badge
-   toast notification
-   skeleton loading
-   empty state
-   error state

------------------------------------------------------------------------

# 31. RESPONSIVE

Pastikan berfungsi pada:

``` text
Mobile
Tablet
Laptop
Desktop
```

Customer dashboard harus nyaman digunakan dari HP.

Admin dashboard harus nyaman digunakan dari desktop tetapi tetap
responsive.

------------------------------------------------------------------------

# 32. SEO WEBSITE

Website publik harus memiliki:

-   title
-   meta description
-   Open Graph
-   canonical
-   sitemap
-   robots.txt
-   structured data jika sesuai
-   SEO-friendly URL

Dashboard tidak perlu di-index search engine.

------------------------------------------------------------------------

# 33. PERFORMANCE

Perhatikan:

-   database indexes
-   pagination
-   eager loading
-   caching
-   Redis
-   queue
-   lazy loading
-   frontend code splitting
-   API pagination
-   image optimization

Jangan mengambil seluruh customer/service sekaligus jika datanya besar.

------------------------------------------------------------------------

# 34. BACKGROUND JOBS

Gunakan Laravel Queue untuk:

-   provisioning VPS
-   provisioning hosting
-   domain registration
-   domain renewal
-   payment processing
-   email
-   WhatsApp notification
-   expiration reminder
-   suspend service
-   terminate service
-   synchronization external API

Gunakan Scheduler untuk pekerjaan periodik.

Contoh:

``` text
billing:generate
billing:reminder
services:check-expiration
services:suspend-expired
domains:sync
proxmox:sync
```

------------------------------------------------------------------------

# 35. TESTING

Buat testing minimal:

### Unit Test

Untuk:

-   pricing
-   billing calculation
-   coupon
-   service lifecycle
-   provisioning logic

### Feature Test

Untuk:

-   registration
-   login
-   order
-   payment callback
-   domain search
-   VPS provisioning
-   customer authorization
-   admin authorization

### Integration Test

Untuk external provider menggunakan mock/fake.

Jangan melakukan transaksi nyata ketika automated test.

------------------------------------------------------------------------

# 36. DEVOPS

Sediakan dokumentasi deployment:

``` text
Ubuntu
↓
Nginx
↓
Laravel
↓
PHP-FPM
↓
MySQL
↓
Redis
↓
Queue Worker
↓
Scheduler
```

Supervisor digunakan untuk queue worker.

SSL menggunakan Let's Encrypt.

Buat `.env.example`.

Jangan commit:

``` text
.env
API keys
password
tokens
private credentials
```

------------------------------------------------------------------------

# 37. CONFIGURATION

Semua credential/provider harus menggunakan environment variable.

Contoh:

``` env
APP_URL=

DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

REDIS_HOST=

PROXMOX_HOST=
PROXMOX_TOKEN_ID=
PROXMOX_TOKEN_SECRET=

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=

DOMAIN_PROVIDER_API_URL=
DOMAIN_PROVIDER_USERNAME=
DOMAIN_PROVIDER_PASSWORD=
```

Jangan menaruh credential langsung di source code.

------------------------------------------------------------------------

# 38. LOGGING & MONITORING

Buat logging untuk:

-   Payment
-   Provisioning
-   Domain API
-   Proxmox API
-   Authentication
-   Webhook
-   Queue failure

Admin dapat melihat status job dan error provisioning.

------------------------------------------------------------------------

# 39. IDEMPOTENCY

Ini WAJIB untuk:

-   Payment webhook
-   Domain registration
-   VPS provisioning
-   Hosting provisioning
-   Renewal

Jika webhook dikirim dua kali, sistem tidak boleh membuat:

``` text
2 invoice payment
2 VPS
2 domain registration
```

Gunakan unique reference/idempotency key.

------------------------------------------------------------------------

# 40. SERVICE LIFECYCLE

Buat state machine/logical lifecycle yang jelas.

Contoh:

``` text
Pending
↓
Active
↓
Expiring
↓
Grace Period
↓
Suspended
↓
Terminated
```

Customer tidak boleh mengaktifkan kembali service sendiri jika status
membutuhkan pembayaran.

------------------------------------------------------------------------

# 41. ADMIN SETTINGS

Admin dapat mengatur:

-   Company name
-   Logo
-   Email
-   WhatsApp
-   Address
-   Currency
-   Tax
-   Invoice prefix
-   Invoice numbering
-   Payment gateways
-   Domain provider
-   Proxmox server
-   Billing rules
-   Grace period
-   Suspension rules
-   Notification templates

------------------------------------------------------------------------

# 42. MULTI-SERVER

Arsitektur harus siap untuk lebih dari satu server.

Contoh:

``` text
Proxmox Cluster A
├── Node 1
├── Node 2
└── Node 3

Proxmox Cluster B
├── Node 1
└── Node 2
```

Sistem harus dapat memilih server/node berdasarkan:

-   availability
-   resource
-   product configuration
-   admin assignment

Jangan membuat sistem hanya bergantung pada satu Proxmox host.

------------------------------------------------------------------------

# 43. FUTURE DEVELOPMENT

Arsitektur harus memungkinkan penambahan:

-   Reseller
-   Affiliate
-   WHMCS migration/import
-   cPanel
-   DirectAdmin
-   Plesk
-   Object Storage
-   SSL Certificate
-   Email Hosting
-   Dedicated Server
-   Game Server
-   IPv4 marketplace
-   API reseller
-   Mobile app

Jangan membangun arsitektur yang sulit diperluas.

------------------------------------------------------------------------

# 44. WORKFLOW DEVELOPMENT

Jangan langsung membuat seluruh sistem sekaligus.

Gunakan fase:

## Phase 1 --- Foundation

-   Project setup
-   Database
-   Authentication
-   Role
-   API structure
-   Frontend structure
-   Layout
-   Theme
-   Settings

## Phase 2 --- Public Website

-   Homepage
-   Product pages
-   Domain search
-   Pricing
-   Login/register

## Phase 3 --- Customer

-   Dashboard
-   Profile
-   Services
-   Orders
-   Invoices
-   Payments

## Phase 4 --- Admin

-   Dashboard
-   Customers
-   Products
-   Orders
-   Services
-   Billing

## Phase 5 --- Payment

-   Gateway abstraction
-   Payment creation
-   Webhook
-   Verification
-   Idempotency

## Phase 6 --- Domain

-   Provider abstraction
-   Search
-   Availability
-   Registration
-   Renewal
-   Nameserver

## Phase 7 --- Hosting

-   Product
-   Server
-   Provisioning abstraction
-   Service management

## Phase 8 --- VPS

-   Proxmox integration
-   Server management
-   VPS provisioning
-   VPS actions
-   Monitoring

## Phase 9 --- Automation

-   Billing scheduler
-   Reminder
-   Suspend
-   Renewal
-   Termination
-   Notifications

## Phase 10 --- Hardening

-   Security
-   Tests
-   Performance
-   Logging
-   Backup
-   Deployment
-   Monitoring

------------------------------------------------------------------------

# 45. ATURAN PENTING UNTUK AI

Anda bertindak sebagai engineer yang mengerjakan project secara
bertahap.

### Jangan:

-   langsung membuat semua file sekaligus
-   mengarang API provider
-   mengarang endpoint Proxmox
-   menyimpan secret di frontend
-   menaruh business logic besar di controller
-   mengabaikan error handling
-   mengabaikan authorization
-   membuat data dummy seolah-olah production
-   membuat provisioning synchronous jika seharusnya queue
-   menghapus kode existing tanpa alasan
-   melakukan perubahan besar tanpa menjelaskan dampaknya

### Wajib:

1.  Analisis project terlebih dahulu.
2.  Periksa struktur file yang sudah ada.
3.  Buat rencana sebelum implementasi.
4.  Tentukan dependency.
5.  Tentukan database schema.
6.  Tentukan API contract.
7.  Implementasikan per fase.
8.  Setelah setiap fase, jalankan test/build/lint yang relevan.
9.  Perbaiki error sebelum lanjut.
10. Dokumentasikan perubahan.
11. Jangan mengubah teknologi utama tanpa alasan.
12. Jika informasi provider belum tersedia, buat adapter/interface dan
    mock, jangan mengarang API.
13. Semua secret menggunakan `.env`.
14. Semua endpoint sensitif harus memiliki authorization backend.
15. Semua webhook harus idempotent.
16. Semua proses provisioning harus dapat dilacak statusnya.
17. Gunakan transaction database jika operasi membutuhkan atomicity.
18. Gunakan queue untuk pekerjaan eksternal yang lama.
19. Buat migration dan seeder yang reproducible.
20. Pastikan aplikasi dapat dijalankan dari fresh installation.

------------------------------------------------------------------------

# 46. OUTPUT YANG HARUS DIBUAT AI

Sebelum coding, buat:

``` text
docs/
├── ARCHITECTURE.md
├── DATABASE.md
├── API.md
├── BILLING.md
├── PROVISIONING.md
├── SECURITY.md
├── DEPLOYMENT.md
└── ROADMAP.md
```

Dan buat:

``` text
README.md
.env.example
```

README harus menjelaskan:

-   requirements
-   installation
-   environment
-   database setup
-   migration
-   seed
-   frontend build
-   queue
-   scheduler
-   deployment

------------------------------------------------------------------------

# 47. DEFINITION OF DONE

Sebuah fitur dianggap selesai hanya jika:

-   UI selesai
-   API selesai
-   database selesai
-   validation selesai
-   authorization selesai
-   error handling selesai
-   loading state selesai
-   empty state selesai
-   logging jika diperlukan
-   test dibuat
-   migration dapat dijalankan
-   tidak ada hard-coded secret
-   build berhasil
-   test berhasil atau error yang tersisa sudah dijelaskan

------------------------------------------------------------------------

# 48. CARA MULAI

Mulai dengan **ANALISIS DAN PERENCANAAN**, bukan langsung coding.

Langkah pertama:

1.  Periksa project saat ini.
2.  Identifikasi framework/versi yang digunakan.
3.  Identifikasi struktur frontend/backend.
4.  Identifikasi database.
5.  Identifikasi dependency.
6.  Identifikasi konfigurasi existing.
7.  Buat architecture proposal.
8.  Buat database ERD.
9.  Buat API plan.
10. Buat roadmap fase implementasi.
11. Tunjukkan file yang akan dibuat/diubah.
12. Tunggu persetujuan sebelum melakukan perubahan besar.

Jika project masih kosong, buat fondasi dari awal sesuai stack:

``` text
Laravel
+
Vue 3
+
TypeScript
+
Vite
+
Tailwind CSS
+
MySQL/MariaDB
+
Redis
```

Target akhir:

> Platform penjualan Domain + Hosting + VPS yang modern, aman, scalable,
> memiliki website publik, customer dashboard, admin dashboard, billing
> otomatis, payment gateway, domain registrar integration, dan
> provisioning VPS otomatis melalui Proxmox API.

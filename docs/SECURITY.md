# Security Architecture

## Authentication

### Fortify (Session-based — Web/Inertia)
- Login dengan email + password
- Rate limiting: 5 attempts per minute per email+IP
- Password hashing: bcrypt (12 rounds)
- Session lifetime: 120 menit (configurable)
- Password confirmation timeout: 3 jam

### Two-Factor Authentication (TOTP)
- Time-based One-Time Password via authenticator app
- Recovery codes (one-time use, 8 codes)
- Password confirmation before 2FA setup changes
- Rate limited: separate throttle for 2FA attempts

### Passkeys / WebAuthn
- Passwordless authentication via biometric/device PIN
- Relying Party ID bound to app domain
- Origin allowlist: configured app URL only
- User handle secret: `PASSKEYS_USER_HANDLE_SECRET` env variable

### API Authentication (Future)
- Laravel Sanctum untuk SPA authentication (cookie-based)
- Laravel Sanctum tokens untuk API reseller / mobile app
- Token abilities scoped per-role

## Authorization

### Role-Based Access Control
| Role | Scope |
|---|---|
| Customer | Own services, invoices, tickets only |
| Admin | All customers, services, products, settings |
| Super Admin | Admin + manage admin users, system config |

### Policy Enforcement
- Setiap model memiliki Policy class
- Controller actions menggunakan `$this->authorize()`
- Middleware `can:` untuk route-level authorization
- Backend enforcement — tidak cukup hanya hide UI

```php
// Contoh: VpsServicePolicy.php
public function view(User $user, VpsService $vps): bool
{
    return $user->isAdmin() || $user->id === $vps->user_id;
}

public function start(User $user, VpsService $vps): bool
{
    return $user->isAdmin() || ($user->id === $vps->user_id && $vps->isActive());
}
```

### Route Protection
```php
// Customer endpoints
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/api/v1/services', [ServiceController::class, 'index']);
});

// Admin endpoints
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/api/v1/admin/customers', [AdminCustomerController::class, 'index']);
});
```

## Input Validation

### Form Requests
- Semua input divalidasi via Form Request classes
- Tidak ada validasi inline di controller
- Whitelist approach — hanya field yang diizinkan yang diterima

### File Uploads
- MIME type validation (bukan hanya ekstensi)
- File size limits per-endpoint
- Upload ke disk `local` atau `s3` — tidak ke `public`
- Attachment ticket: max 5MB, allowed types: pdf, jpg, png, zip

## Webhook Security

### Payment Gateway Webhooks
1. **Signature verification** — setiap payload diverifikasi dengan secret key
2. **Idempotency** — gunakan `transaction_id` / `order_id` sebagai key
3. **Raw payload logging** — simpan payload mentah untuk audit
4. **Duplicate prevention** — check database sebelum proses ulang
5. **Atomic operations** — gunakan database transaction

```php
public function handleWebhook(Request $request): JsonResponse
{
    $payload = $request->all();

    // 1. Save raw payload
    WebhookLog::create([
        'provider' => 'midtrans',
        'payload' => $payload,
    ]);

    // 2. Verify signature
    if (!$this->verifySignature($payload)) {
        return response()->json(['status' => 'invalid_signature'], 400);
    }

    // 3. Check idempotency
    $orderId = $payload['order_id'];
    if (Payment::where('transaction_id', $orderId)->where('status', 'paid')->exists()) {
        return response()->json(['status' => 'already_processed']);
    }

    // 4. Process in transaction
    DB::transaction(function () use ($payload) {
        // Mark invoice paid, activate service
    });

    return response()->json(['status' => 'ok']);
}
```

## Credential Management

### Environment Variables
- Semua credential provider disimpan di `.env`
- Tidak ada hard-coded API key di source code
- `.env` tidak di-commit (ada di `.gitignore`)

### Required Environment Variables
```env
# Proxmox
PROXMOX_HOST=
PROXMOX_TOKEN_ID=
PROXMOX_TOKEN_SECRET=

# Payment Gateways
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_MERCHANT_ID=

XENDIT_API_KEY=
XENDIT_CALLBACK_TOKEN=

DOKU_CLIENT_ID=
DOKU_SECRET_KEY=

# Domain Registrar
DOMAIN_PROVIDER_API_URL=
DOMAIN_PROVIDER_USERNAME=
DOMAIN_PROVIDER_PASSWORD=

# Encryption
APP_KEY=                    # Auto-generated, never shared
PASSKEYS_USER_HANDLE_SECRET=
```

### Provider Credentials in Database
Jika provider credentials perlu disimpan di database (multi-server scenario):
- Encrypt menggunakan Laravel encryption (`Crypt::encrypt()`)
- Decrypt hanya saat digunakan
- Tidak di-expose ke frontend atau API response
- Hanya admin yang bisa mengelola

## Rate Limiting

| Endpoint | Limit | Window |
|---|---|---|
| Login | 5 | 1 min (per email+IP) |
| 2FA verify | 5 | 1 min |
| Register | 3 | 1 hour (per IP) |
| Password reset request | 3 | 1 hour (per email) |
| API (authenticated) | 60 | 1 min |
| API (public) | 30 | 1 min |
| Webhook | 100 | 1 min |
| Checkout | 10 | 1 min |

## XSS & CSRF Protection

### XSS
- Vue template auto-escaping (default)
- Inertia handles XSRF token automatically
- CSP headers via Nginx configuration
- `v-html` dihindari; jika perlu, sanitize input

### CSRF
- Laravel CSRF token via cookie
- Inertia auto-includes `X-XSRF-TOKEN` header
- `SameSite=Lax` pada session cookie
- Webhook routes excluded dari CSRF middleware

## SQL Injection
- Eloquent ORM → parameterized queries by default
- Jika menggunakan raw queries: `DB::select('...', $bindings)` — selalu binding
- Tidak ada string interpolation di query SQL

## Audit Logging

Semua aksi penting log ke `audit_logs` table:
- User management (create, delete, role change)
- Payment status changes
- Service provisioning (success/failure)
- Config changes (settings, pricing)
- Security events (password change, 2FA enable/disable, login failure)

## Error Handling

- External API error → graceful failure, tidak crash
- Customer-facing: pesan generik (bukan raw error)
- Admin-facing: detail error di log/dashboard
- Semua external API call memiliki: timeout, retry, logging

## Infrastructure Security

- Nginx: SSL/TLS (Let's Encrypt), HTTP/2, security headers
- PHP-FPM: isolated pool per-site
- Database: firewall, no remote root access
- Queue worker: Supervisor restarts on failure
- Backup: daily database + file backup ke lokasi terpisah
# Billing System Design

## Billing Cycles

| Cycle | Duration | Multiplier |
|---|---|---|
| Monthly | 1 bulan | 1× |
| Quarterly | 3 bulan | 3× |
| Semi-annually | 6 bulan | 6× |
| Annually | 12 bulan | 12× |
| Biennially | 24 bulan | 24× |
| Triennially | 36 bulan | 36× |

## Invoice Lifecycle

```
┌──────┐    ┌─────────┐    ┌──────┐    ┌─────────┐    ┌───────────┐    ┌──────────┐
│Draft │───>│ Pending │───>│ Paid │───>│ Refunded│    │ Cancelled │    │ Expired  │
└──────┘    └────┬────┘    └──────┘    └─────────┘    └───────────┘    └──────────┘
                 │                                        ▲               ▲
                 │                                        │               │
                 └────────────────────────────────────────┘               │
                 (admin cancel)                                            │
                                                                          │
                 ┌────────────────────────────────────────────────────────┘
                 (due_date passed, no payment)
```

### Status Transitions
| From | To | Trigger |
|---|---|---|
| — | Draft | Admin creates manual invoice |
| Draft | Pending | Invoice sent to customer |
| — | Pending | Auto-generated on order checkout / renewal |
| Pending | Paid | Payment received (webhook) |
| Pending | Expired | Due date passed, no payment |
| Pending | Cancelled | Admin cancel invoice |
| Paid | Refunded | Admin process refund |

## Invoice Numbering

```
Format: INV-{YYYY}{MM}-{SEQ:5}
Example: INV-202509-00001
```

Configurable via admin settings:
- Prefix: `INV`
- Date format in number: `{YYYY}{MM}`
- Sequence length: 5 digits, auto-increment per month

## Auto-Billing Flow

### Renewal Schedule

```
Service expires: 30 September
│
├── H-14: Generate renewal invoice (billing:generate)
│         Invoice due date = expiration date
│
├── H-7:  Send reminder (billing:reminder)
├── H-3:  Send reminder
├── H-1:  Send reminder
│
├── H+0:  Due date. If unpaid:
│         → Service enters Grace Period
│
├── H+3:  Grace Period ends (configurable). If still unpaid:
│         → Suspend service
│
├── H+7:  Suspension period ends. If still unpaid:
│         → Terminate service
```

### Grace Period Configuration (Admin Settings)

| Setting | Default | Description |
|---|---|---|
| `billing.grace_period_days` | 3 | Days after due date before suspension |
| `billing.suspension_period_days` | 7 | Days after suspension before termination |
| `billing.reminder_days` | [7, 3, 1] | Days before due date to send reminders |
| `billing.auto_renew` | true | Auto-generate renewal invoice |

## Tax Calculation

### Tax Configuration
```php
// Admin settings
'tax' => [
    'enable' => true,
    'rate' => 11.00,         // PPN 11%
    'label' => 'PPN',
    'apply_to' => ['hosting', 'vps', 'domain'],  // product types
],
```

### Calculation
```
Subtotal = sum(item.unit_price × item.quantity)
Discount = coupon_discount(subtotal)
Tax = (subtotal - discount) × tax_rate
Total = subtotal - discount + tax
```

Example:
```
Subtotal: Rp 100.000
Discount (20%): -Rp 20.000
PPN (11%): +Rp 8.800
Total: Rp 88.800
```

## Coupon System

### Coupon Types
| Type | Example | Behavior |
|---|---|---|
| Percentage | `WELCOME20` = 20% OFF | `discount = subtotal × value / 100` |
| Fixed | `FLAT50K` = Rp 50.000 OFF | `discount = min(value, subtotal)` |

### Coupon Rules
- `min_order` — minimum order amount to apply
- `max_usage` — total usage limit across all customers (null = unlimited)
- `per_user_limit` — max usage per customer (default 1)
- `starts_at` / `expires_at` — validity period
- `product_ids` — restrict to specific products (null = all)
- `is_active` — manual enable/disable

### Validation at Checkout
```php
class CouponValidator
{
    public function validate(Coupon $coupon, User $user, Order $order): ValidationResult
    {
        // 1. Check active + date range
        if (!$coupon->isActive() || !$coupon->isInDateRange()) {
            return ValidationResult::invalid('Coupon is not active');
        }

        // 2. Check min order
        if ($order->subtotal < $coupon->min_order) {
            return ValidationResult::invalid('Minimum order not met');
        }

        // 3. Check per-user limit
        $usageCount = CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)->count();
        if ($usageCount >= $coupon->per_user_limit) {
            return ValidationResult::invalid('Coupon usage limit reached');
        }

        // 4. Check total usage
        if ($coupon->max_usage) {
            $totalUsage = CouponUsage::where('coupon_id', $coupon->id)->count();
            if ($totalUsage >= $coupon->max_usage) {
                return ValidationResult::invalid('Coupon fully redeemed');
            }
        }

        // 5. Check product restriction
        if ($coupon->product_ids) {
            $orderProductIds = $order->items->pluck('product_id');
            $allowed = array_intersect($orderProductIds->toArray(), $coupon->product_ids);
            if (empty($allowed)) {
                return ValidationResult::invalid('Coupon not applicable to these products');
            }
        }

        return ValidationResult::valid();
    }
}
```

## Payment Flow

```
Customer clicks "Pay Invoice"
        │
        ▼
Choose payment method (bank transfer, VA, QRIS, etc.)
        │
        ▼
POST /api/v1/payments/create
        │
        ▼
PaymentGatewayInterface::createPayment()
        │
        ▼
Return payment URL / VA number / QR code
        │
        ▼
Customer completes payment at gateway
        │
        ▼
Gateway sends webhook → /api/v1/webhooks/{gateway}
        │
        ▼
Verify signature → Check idempotency → Mark invoice paid
        │
        ▼
Fire PaymentReceived event → Activate/Provision service
```

## Recurring Billing Scheduler

### `billing:generate` (Runs daily)

```php
// Pseudocode
$expiringServices = Service::where('expired_at', '<=', now()->addDays(14))
    ->where('status', 'active')
    ->whereDoesntHave('invoices', function ($q) {
        $q->where('status', 'pending')->where('due_date', '>=', now());
    })
    ->get();

foreach ($expiringServices as $service) {
    GenerateRenewalInvoiceJob::dispatch($service);
}
```

### `billing:reminder` (Runs daily)
- Cari unpaid invoice dengan due_date H-7, H-3, H-1
- Kirim notifikasi email ke customer

### `services:suspend-expired` (Runs daily)
- Cari service dengan status `grace_period` yang sudah lewat grace period
- Dispatch `SuspendServiceJob`

### `services:terminate-expired` (Runs daily)
- Cari service dengan status `suspended` yang sudah lewat masa suspend
- Dispatch `TerminateServiceJob`

## Refund Flow

```
Admin opens invoice (status: paid)
        │
        ▼
Clicks "Refund" → Masukkan amount (partial/full) + reason
        │
        ▼
PaymentGatewayInterface::refund($payment, $amount)
        │
        ▼
Gateway processes refund
        │
        ▼
Update payment status → 'refunded'
Update invoice status → 'refunded'
        │
        ▼
Log audit
```

## Invoice PDF Generation

Gunakan Laravel package (seperti `barryvdh/laravel-dompdf` atau `laravel-daily/Invoices`) untuk generate PDF invoice dengan template:
- Company logo + info
- Customer info
- Invoice number + date + due date
- Item table (description, qty, unit price, total)
- Subtotal, discount, tax, total
- Payment instructions
- Footer (terms, company registration)

## Database Transactions

Semua operasi billing yang melibatkan multiple writes menggunakan `DB::transaction()`:
- Checkout: create order → create order_items → create invoice → create invoice_items
- Payment received: update payment → update invoice → update order → create service
- Refund: create refund record → update payment → update invoice
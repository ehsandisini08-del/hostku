<?php

namespace App\Services\Order;

use App\Models\HostingServer;
use App\Models\HostingService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\Hosting\HostingProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected HostingProvisioningService $provisioningService,
    ) {}

    public function createHostingOrder(User $user, Product $product, string $billingCycle, string $domain): Invoice
    {
        if ($product->type !== 'hosting' || ! $product->is_active) {
            throw new InvalidArgumentException('Produk hosting tidak valid atau tidak aktif.');
        }

        $priceRecord = $product->prices()->where('billing_cycle', $billingCycle)->first();
        if (! $priceRecord) {
            throw new InvalidArgumentException("Siklus pembayaran {$billingCycle} tidak tersedia untuk paket ini.");
        }

        $unitPrice = $priceRecord->is_promo && $priceRecord->promo_price !== null
            ? (float) $priceRecord->promo_price
            : (float) $priceRecord->price;
        $setupFee = (float) ($priceRecord->setup_fee ?? 0);
        $total = $unitPrice + $setupFee;

        $cleanDomain = strtolower(trim($domain));

        return DB::transaction(function () use ($user, $product, $billingCycle, $cleanDomain, $unitPrice, $setupFee, $total) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'status' => 'pending',
                'subtotal' => $unitPrice,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $total,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'description' => "{$product->name} ({$cleanDomain}) - ".ucfirst($billingCycle),
                'billing_cycle' => $billingCycle,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'setup_fee' => $setupFee,
                'total' => $total,
                'meta' => [
                    'domain' => $cleanDomain,
                    'hosting_plan_id' => $product->hostingPlan?->id,
                ],
            ]);

            $periodEnd = $billingCycle === 'annually' ? now()->addYear() : now()->addMonth();

            $invoice = Invoice::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'invoice_number' => 'INV-'.strtoupper(Str::random(8)),
                'status' => 'pending',
                'subtotal' => $unitPrice,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $total,
                'due_date' => now()->addDays(3)->toDateString(),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "{$product->name} ({$cleanDomain}) - ".ucfirst($billingCycle),
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'total' => $total,
                'period_start' => now()->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ]);

            return $invoice;
        });
    }

    public function handlePendingHostingOrder(User $user, ?Request $request = null): ?Invoice
    {
        $session = $request ? $request->session() : session();

        if (! $session->has('pending_hosting_order')) {
            return null;
        }

        $pending = $session->pull('pending_hosting_order');
        $productId = $pending['product_id'] ?? null;
        $billingCycle = $pending['billing_cycle'] ?? null;
        $domain = $pending['domain'] ?? null;

        if (! $productId || ! $billingCycle || ! $domain) {
            return null;
        }

        $product = Product::with(['hostingPlan', 'prices'])->find($productId);

        if (! $product || ! $product->is_active) {
            return null;
        }

        return $this->createHostingOrder(
            user: $user,
            product: $product,
            billingCycle: $billingCycle,
            domain: $domain,
        );
    }

    public function fulfillOrder(Order $order): void
    {
        $order->loadMissing(['items.product.hostingPlan', 'user']);

        foreach ($order->items as $item) {
            if ($item->product?->type === 'hosting') {
                $this->fulfillHostingItem($order, $item);
            }
        }
    }

    protected function fulfillHostingItem(Order $order, OrderItem $item): void
    {
        $domain = $item->meta['domain'] ?? null;
        if (! $domain) {
            return;
        }

        $plan = $item->product?->hostingPlan;
        if (! $plan) {
            Log::warning("No hosting plan found for product #{$item->product_id}");

            return;
        }

        // Idempotency check: don't create service if already exists for this domain & order
        $existing = HostingService::where('domain', $domain)
            ->whereHas('service', fn ($q) => $q->where('order_id', $order->id))
            ->first();

        if ($existing) {
            return;
        }

        $server = HostingServer::where('is_active', true)->first();
        if (! $server) {
            Log::error("Cannot provision hosting for order #{$order->id}: No active hosting server available.");

            return;
        }

        $expiredAt = $item->billing_cycle === 'annually' ? now()->addYear() : now()->addMonth();

        DB::transaction(function () use ($order, $plan, $server, $domain, $expiredAt) {
            $hostingService = HostingService::create([
                'hosting_plan_id' => $plan->id,
                'hosting_server_id' => $server->id,
                'domain' => $domain,
                'server_ip' => $server->ip_address,
            ]);

            $service = Service::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'serviceable_type' => HostingService::class,
                'serviceable_id' => $hostingService->id,
                'status' => 'pending',
                'expired_at' => $expiredAt,
            ]);

            try {
                $this->provisioningService->provision($hostingService, $domain);
                $service->update(['status' => 'active']);
            } catch (\Throwable $e) {
                Log::error("Auto provisioning error for domain {$domain}: ".$e->getMessage(), [
                    'order_id' => $order->id,
                    'exception' => $e,
                ]);
                $service->update(['status' => 'suspended']);
            }
        });
    }
}

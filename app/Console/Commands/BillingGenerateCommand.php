<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use Illuminate\Console\Command;

class BillingGenerateCommand extends Command
{
    protected $signature = 'billing:generate';

    protected $description = 'Generate renewal invoices for services expiring within 14 days';

    public function handle(): void
    {
        $services = Service::where('expired_at', '<=', now()->addDays(14))
            ->where('expired_at', '>', now())
            ->where('status', 'active')
            ->get();

        foreach ($services as $service) {
            $existingInvoice = Invoice::whereHas('items', fn ($q) => $q->where('service_id', $service->id))
                ->where('status', 'pending')
                ->where('due_date', '>=', $service->expired_at)
                ->exists();

            if ($existingInvoice) {
                continue;
            }

            $invoice = Invoice::create([
                'user_id' => $service->user_id,
                'order_id' => $service->order_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => 'pending',
                'subtotal' => $this->getServicePrice($service),
                'total' => $this->getServicePrice($service),
                'due_date' => $service->expired_at,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => $this->getServiceDescription($service),
                'quantity' => 1,
                'unit_price' => $this->getServicePrice($service),
                'total' => $this->getServicePrice($service),
            ]);
        }

        $this->info("Generated invoices for {$services->count()} services.");
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';

        $last = Invoice::where('invoice_number', 'like', $prefix.'%')->latest('id')->first();

        $seq = $last ? (int) substr($last->invoice_number, -5) + 1 : 1;

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    private function getServicePrice(Service $service): float
    {
        $orderItem = $service->order->items()->first();

        return $orderItem ? (float) $orderItem->unit_price : 0;
    }

    private function getServiceDescription(Service $service): string
    {
        $type = class_basename($service->serviceable_type ?? '');

        return "{$type} Service Renewal — #{$service->id}";
    }
}

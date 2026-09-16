<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Payment;

class PaymentService
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];

    public function register(string $name, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$name] = $gateway;
    }

    public function gateway(string $name): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$name])) {
            throw new \InvalidArgumentException("Payment gateway '{$name}' not registered.");
        }

        return $this->gateways[$name];
    }

    public function createPayment(Invoice $invoice, string $gateway, array $options = []): PaymentResult
    {
        $g = $this->gateway($gateway);
        $result = $g->createPayment($invoice, $options);

        if ($result->success) {
            Payment::create([
                'invoice_id' => $invoice->id,
                'transaction_id' => $result->transactionId,
                'gateway' => $gateway,
                'amount' => $invoice->total,
                'status' => 'pending',
                'payment_method' => $options['payment_method'] ?? null,
                'payment_channel' => $options['payment_channel'] ?? null,
            ]);
        }

        return $result;
    }

    public function handleCallback(string $gateway, array $payload): CallbackResult
    {
        $g = $this->gateway($gateway);
        $result = $g->handleCallback($payload);

        if ($result->processed && $result->status === 'success') {
            $payment = Payment::where('transaction_id', $result->transactionId)->first();
            if ($payment && $payment->status !== 'paid') {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'raw_response' => $payload,
                ]);

                $invoice = $payment->invoice;
                $invoice->update(['status' => 'paid', 'paid_at' => now(), 'payment_method' => $payment->payment_method]);

                $order = $invoice->order;
                if ($order) {
                    $order->update(['status' => 'paid']);
                }
            }
        }

        return $result;
    }
}

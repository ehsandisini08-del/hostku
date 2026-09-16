<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;

class MidtransAdapter implements PaymentGatewayInterface
{
    public function __construct(
        private string $serverKey,
        private string $clientKey,
        private string $merchantId,
        private bool $isProduction = false,
    ) {}

    public function createPayment(Invoice $invoice, array $options = []): PaymentResult
    {
        $baseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $transactionId = 'MID-'.$invoice->invoice_number.'-'.time();

        $payload = [
            'transaction_details' => [
                'order_id' => $transactionId,
                'gross_amount' => (int) $invoice->total,
            ],
            'customer_details' => [
                'first_name' => $invoice->user->name,
                'email' => $invoice->user->email,
            ],
            'items' => $invoice->items->map(fn ($item) => [
                'name' => $item->description,
                'quantity' => $item->quantity,
                'price' => (int) $item->unit_price,
            ])->toArray(),
        ];

        if ($options['payment_channel'] ?? null) {
            $payload['enabled_payments'] = [$options['payment_channel']];
        }

        $response = Http::withBasicAuth($this->serverKey, '')
            ->withOptions(['timeout' => 30])
            ->post($baseUrl, $payload);

        if (! $response->successful()) {
            return new PaymentResult(
                success: false,
                transactionId: $transactionId,
                errorMessage: $response->body(),
                rawResponse: $response->json(),
            );
        }

        $data = $response->json();

        return new PaymentResult(
            success: true,
            transactionId: $transactionId,
            redirectUrl: $data['redirect_url'] ?? null,
            paymentUrl: $data['redirect_url'] ?? null,
            rawResponse: $data,
        );
    }

    public function verifyPayment(string $transactionId): PaymentVerification
    {
        $baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $response = Http::withBasicAuth($this->serverKey, '')
            ->withOptions(['timeout' => 15])
            ->get("{$baseUrl}/{$transactionId}/status");

        if (! $response->successful()) {
            return new PaymentVerification(paid: false, transactionId: $transactionId);
        }

        $data = $response->json();

        return new PaymentVerification(
            paid: in_array($data['transaction_status'] ?? '', ['settlement', 'capture']),
            transactionId: $transactionId,
            paymentMethod: $data['payment_type'] ?? null,
            amount: ($data['gross_amount'] ?? null) ? (float) $data['gross_amount'] : null,
            paidAt: $data['settlement_time'] ?? null,
            rawResponse: $data,
        );
    }

    public function handleCallback(array $payload): CallbackResult
    {
        if (($payload['status_code'] ?? '') === '200') {
            return new CallbackResult(
                processed: true,
                transactionId: $payload['order_id'],
                status: 'success',
                amount: isset($payload['gross_amount']) ? (float) $payload['gross_amount'] : null,
            );
        }

        return new CallbackResult(
            processed: true,
            transactionId: $payload['order_id'],
            status: 'failed',
        );
    }

    public function refund(Payment $payment, ?float $amount = null): RefundResult
    {
        $baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $response = Http::withBasicAuth($this->serverKey, '')
            ->withOptions(['timeout' => 15])
            ->post("{$baseUrl}/{$payment->transaction_id}/refund", [
                'amount' => $amount ?? $payment->amount,
            ]);

        if (! $response->successful()) {
            return new RefundResult(
                success: false,
                refundId: '',
                errorMessage: $response->body(),
            );
        }

        $data = $response->json();

        return new RefundResult(
            success: true,
            refundId: $data['refund_key'] ?? uniqid('RF-'),
            amount: $amount ?? $payment->amount,
        );
    }

    public function getPaymentStatus(string $transactionId): string
    {
        return $this->verifyPayment($transactionId)->paid ? 'paid' : 'pending';
    }
}

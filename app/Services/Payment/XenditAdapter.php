<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;

class XenditAdapter implements PaymentGatewayInterface
{
    public function __construct(
        private string $apiKey,
        private string $callbackToken,
    ) {}

    public function createPayment(Invoice $invoice, array $options = []): PaymentResult
    {
        $transactionId = 'XND-'.$invoice->invoice_number.'-'.time();

        $payload = [
            'external_id' => $transactionId,
            'amount' => (float) $invoice->total,
            'payer_email' => $invoice->user->email,
            'description' => 'Invoice '.$invoice->invoice_number,
        ];

        $endpoint = match ($options['payment_method'] ?? 'virtual_account') {
            'virtual_account' => '/invoices',
            'ewallet' => '/ewallets/charges',
            'qr_code' => '/qr_codes',
            default => '/invoices',
        };

        $response = Http::withBasicAuth($this->apiKey, '')
            ->withOptions(['timeout' => 30])
            ->post('https://api.xendit.co'.$endpoint, $payload);

        if (! $response->successful()) {
            return new PaymentResult(
                success: false,
                transactionId: $transactionId,
                errorMessage: $response->body(),
            );
        }

        $data = $response->json();

        return new PaymentResult(
            success: true,
            transactionId: $transactionId,
            paymentUrl: $data['invoice_url'] ?? $data['checkout_url'] ?? null,
            rawResponse: $data,
        );
    }

    public function verifyPayment(string $transactionId): PaymentVerification
    {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->withOptions(['timeout' => 15])
            ->get("https://api.xendit.co/invoices/{$transactionId}");

        if (! $response->successful()) {
            return new PaymentVerification(paid: false, transactionId: $transactionId);
        }

        $data = $response->json();

        return new PaymentVerification(
            paid: ($data['status'] ?? '') === 'PAID',
            transactionId: $transactionId,
            paymentMethod: $data['payment_method'] ?? null,
            paymentChannel: $data['payment_channel'] ?? null,
            amount: $data['amount'] ?? null,
            paidAt: $data['paid_at'] ?? null,
            rawResponse: $data,
        );
    }

    public function handleCallback(array $payload): CallbackResult
    {
        if (($payload['status'] ?? '') === 'PAID') {
            return new CallbackResult(
                processed: true,
                transactionId: $payload['external_id'] ?? $payload['id'],
                status: 'success',
                amount: $payload['amount'] ?? null,
            );
        }

        return new CallbackResult(
            processed: true,
            transactionId: $payload['external_id'] ?? $payload['id'],
            status: 'failed',
        );
    }

    public function refund(Payment $payment, ?float $amount = null): RefundResult
    {
        $response = Http::withBasicAuth($this->apiKey, '')
            ->withOptions(['timeout' => 15])
            ->post("https://api.xendit.co/invoices/{$payment->transaction_id}/refund", [
                'amount' => $amount ?? $payment->amount,
            ]);

        return new RefundResult(
            success: $response->successful(),
            refundId: uniqid('RF-'),
            amount: $amount ?? $payment->amount,
            errorMessage: $response->successful() ? null : $response->body(),
        );
    }

    public function getPaymentStatus(string $transactionId): string
    {
        return $this->verifyPayment($transactionId)->paid ? 'paid' : 'pending';
    }
}

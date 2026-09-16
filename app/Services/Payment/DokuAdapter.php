<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;

class DokuAdapter implements PaymentGatewayInterface
{
    public function __construct(
        private string $clientId,
        private string $secretKey,
    ) {}

    public function createPayment(Invoice $invoice, array $options = []): PaymentResult
    {
        $transactionId = 'DOKU-'.$invoice->invoice_number.'-'.time();

        $response = Http::withBasicAuth($this->clientId, $this->secretKey)
            ->withOptions(['timeout' => 30])
            ->post('https://api.doku.com/checkout/v1/payment', [
                'order' => [
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => (float) $invoice->total,
                    'currency' => 'IDR',
                ],
                'customer' => [
                    'name' => $invoice->user->name,
                    'email' => $invoice->user->email,
                ],
            ]);

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
            paymentUrl: $data['payment_url'] ?? null,
            rawResponse: $data,
        );
    }

    public function verifyPayment(string $transactionId): PaymentVerification
    {
        $response = Http::withBasicAuth($this->clientId, $this->secretKey)
            ->withOptions(['timeout' => 15])
            ->get("https://api.doku.com/checkout/v1/status/{$transactionId}");

        if (! $response->successful()) {
            return new PaymentVerification(paid: false, transactionId: $transactionId);
        }

        $data = $response->json();

        return new PaymentVerification(
            paid: ($data['transaction_status'] ?? '') === 'SUCCESS',
            transactionId: $transactionId,
            paymentMethod: $data['payment_name'] ?? null,
            amount: $data['amount'] ?? null,
            paidAt: $data['settlement_time'] ?? null,
            rawResponse: $data,
        );
    }

    public function handleCallback(array $payload): CallbackResult
    {
        if (($payload['TRANSACTIONSTATUS'] ?? '') === 'SUCCESS') {
            return new CallbackResult(
                processed: true,
                transactionId: $payload['TRANSIDMERCHANT'] ?? $payload['WORDS'] ?? '',
                status: 'success',
                amount: isset($payload['AMOUNT']) ? (float) $payload['AMOUNT'] : null,
            );
        }

        return new CallbackResult(
            processed: true,
            transactionId: $payload['TRANSIDMERCHANT'] ?? '',
            status: 'failed',
        );
    }

    public function refund(Payment $payment, ?float $amount = null): RefundResult
    {
        return new RefundResult(
            success: false,
            refundId: '',
            errorMessage: 'DOku refund requires manual processing via merchant portal.',
        );
    }

    public function getPaymentStatus(string $transactionId): string
    {
        return $this->verifyPayment($transactionId)->paid ? 'paid' : 'pending';
    }
}

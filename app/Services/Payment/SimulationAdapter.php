<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Payment;

class SimulationAdapter implements PaymentGatewayInterface
{
    public function createPayment(Invoice $invoice, array $options = []): PaymentResult
    {
        $transactionId = 'SIM-'.$invoice->invoice_number.'-'.time();

        return new PaymentResult(
            success: true,
            transactionId: $transactionId,
            redirectUrl: null,
            vaNumber: '8888'.str_pad((string) $invoice->id, 8, '0', STR_PAD_LEFT),
            qrCodeUrl: null,
            paymentUrl: null,
            rawResponse: [
                'status' => 'pending',
                'channel' => $options['payment_channel'] ?? 'simulation',
            ],
        );
    }

    public function verifyPayment(string $transactionId): PaymentVerification
    {
        return new PaymentVerification(
            paid: true,
            transactionId: $transactionId,
            paymentMethod: 'simulation',
            amount: 0,
            paidAt: now()->toIso8601String(),
        );
    }

    public function handleCallback(array $payload): CallbackResult
    {
        return new CallbackResult(
            processed: true,
            transactionId: $payload['transaction_id'] ?? '',
            status: 'success',
            amount: (float) ($payload['amount'] ?? 0),
            invoiceNumber: $payload['invoice_number'] ?? null,
        );
    }

    public function refund(Payment $refundable, ?float $amount = null): RefundResult
    {
        return new RefundResult(
            success: true,
            refundId: 'REF-'.time(),
            amount: $amount,
        );
    }

    public function getPaymentStatus(string $transactionId): string
    {
        return 'success';
    }
}

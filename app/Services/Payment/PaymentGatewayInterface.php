<?php

namespace App\Services\Payment;

use App\Models\Invoice;

interface PaymentGatewayInterface
{
    public function createPayment(Invoice $invoice, array $options = []): PaymentResult;

    public function verifyPayment(string $transactionId): PaymentVerification;

    public function handleCallback(array $payload): CallbackResult;

    public function refund(Payment $refundable, ?float $amount = null): RefundResult;

    public function getPaymentStatus(string $transactionId): string;
}

class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $transactionId,
        public ?string $redirectUrl = null,
        public ?string $vaNumber = null,
        public ?string $qrCodeUrl = null,
        public ?string $paymentUrl = null,
        public ?array $rawResponse = null,
        public ?string $errorMessage = null,
    ) {}
}

class PaymentVerification
{
    public function __construct(
        public bool $paid,
        public string $transactionId,
        public ?string $paymentMethod = null,
        public ?string $paymentChannel = null,
        public ?float $amount = null,
        public ?string $paidAt = null,
        public ?array $rawResponse = null,
    ) {}
}

class CallbackResult
{
    public function __construct(
        public bool $processed,
        public string $transactionId,
        public string $status,
        public ?float $amount = null,
        public ?string $invoiceNumber = null,
    ) {}
}

class RefundResult
{
    public function __construct(
        public bool $success,
        public string $refundId,
        public ?float $amount = null,
        public ?string $errorMessage = null,
    ) {}
}

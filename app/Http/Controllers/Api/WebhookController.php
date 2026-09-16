<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransactionLog;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function midtrans(Request $request): JsonResponse
    {
        $payload = $request->all();

        PaymentTransactionLog::create([
            'transaction_id' => $payload['order_id'] ?? 'unknown',
            'gateway' => 'midtrans',
            'event_type' => $payload['transaction_status'] ?? 'unknown',
            'raw_payload' => $payload,
        ]);

        $existingLog = PaymentTransactionLog::where('transaction_id', $payload['order_id'] ?? '')
            ->where('processed', true)
            ->exists();

        if ($existingLog) {
            return response()->json(['status' => 'already_processed']);
        }

        $result = $this->paymentService->handleCallback('midtrans', $payload);

        PaymentTransactionLog::where('transaction_id', $result->transactionId)
            ->update(['processed' => true, 'processed_at' => now()]);

        return response()->json(['status' => 'ok']);
    }

    public function xendit(Request $request): JsonResponse
    {
        $payload = $request->all();

        PaymentTransactionLog::create([
            'transaction_id' => $payload['external_id'] ?? $payload['id'] ?? 'unknown',
            'gateway' => 'xendit',
            'event_type' => $payload['status'] ?? 'unknown',
            'raw_payload' => $payload,
        ]);

        $existingLog = PaymentTransactionLog::where('transaction_id', $payload['external_id'] ?? $payload['id'] ?? '')
            ->where('processed', true)
            ->exists();

        if ($existingLog) {
            return response()->json(['status' => 'already_processed']);
        }

        $result = $this->paymentService->handleCallback('xendit', $payload);

        PaymentTransactionLog::where('transaction_id', $result->transactionId)
            ->update(['processed' => true, 'processed_at' => now()]);

        return response()->json(['status' => 'ok']);
    }

    public function doku(Request $request): JsonResponse
    {
        $payload = $request->all();

        PaymentTransactionLog::create([
            'transaction_id' => $payload['TRANSIDMERCHANT'] ?? 'unknown',
            'gateway' => 'doku',
            'event_type' => $payload['TRANSACTIONSTATUS'] ?? 'unknown',
            'raw_payload' => $payload,
        ]);

        $existingLog = PaymentTransactionLog::where('transaction_id', $payload['TRANSIDMERCHANT'] ?? '')
            ->where('processed', true)
            ->exists();

        if ($existingLog) {
            return response()->json(['status' => 'already_processed']);
        }

        $result = $this->paymentService->handleCallback('doku', $payload);

        PaymentTransactionLog::where('transaction_id', $result->transactionId)
            ->update(['processed' => true, 'processed_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Invoice $invoice): Response
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Checkout', [
            'invoice' => $invoice->load('items'),
        ]);
    }

    public function pay(Request $request, Invoice $invoice): Response|RedirectResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'gateway' => ['required', 'in:midtrans,xendit,doku'],
            'payment_method' => ['required', 'string'],
            'payment_channel' => ['nullable', 'string'],
        ]);

        $result = $this->paymentService->createPayment($invoice, $validated['gateway'], [
            'payment_method' => $validated['payment_method'],
            'payment_channel' => $validated['payment_channel'] ?? null,
        ]);

        if (! $result->success) {
            return back()->with('error', $result->errorMessage ?? 'Payment creation failed.');
        }

        return Inertia::render('Checkout', [
            'invoice' => $invoice->load('items'),
            'paymentResult' => $result,
        ]);
    }
}

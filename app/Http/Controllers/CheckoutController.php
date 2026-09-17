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

        if ($invoice->status === 'paid') {
            return redirect()->route('customer.hosting')->with('success', 'Invoice ini sudah lunas. Layanan hosting Anda telah aktif.');
        }

        $validated = $request->validate([
            'gateway' => ['required', 'in:midtrans,xendit,doku,simulation'],
            'payment_method' => ['required', 'string'],
            'payment_channel' => ['nullable', 'string'],
        ]);

        if ($validated['gateway'] === 'simulation') {
            $this->paymentService->markAsPaid(
                $invoice,
                $validated['payment_method'],
                $validated['payment_channel'] ?? null,
            );

            return redirect()->route('customer.hosting')->with('success', 'Pembayaran berhasil dikonfirmasi! Layanan hosting Anda sedang diproses.');
        }

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

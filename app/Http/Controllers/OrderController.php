<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Order\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function storeHostingOrder(Request $request, OrderService $orderService): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'billing_cycle' => ['required', 'in:monthly,annually'],
            'domain' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)+$/',
            ],
        ], [
            'domain.regex' => 'Format nama domain tidak valid (contoh: domainanda.com).',
        ]);

        $product = Product::with(['hostingPlan', 'prices'])->findOrFail($validated['product_id']);

        if ($product->type !== 'hosting' || ! $product->is_active) {
            return back()->with('error', 'Paket hosting tidak valid atau tidak aktif.');
        }

        $invoice = $orderService->createHostingOrder(
            user: $request->user(),
            product: $product,
            billingCycle: $validated['billing_cycle'],
            domain: $validated['domain'],
        );

        return redirect()->route('checkout', $invoice->id)->with('success', 'Pesanan hosting berhasil dibuat. Silakan selesaikan pembayaran.');
    }
}

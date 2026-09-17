<?php

namespace App\Http\Responses;

use App\Services\Order\OrderService;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        if ($user = $request->user()) {
            $invoice = app(OrderService::class)->handlePendingHostingOrder($user, $request);

            if ($invoice) {
                return redirect()->route('checkout', $invoice->id)
                    ->with('success', 'Pesanan hosting berhasil dibuat. Silakan selesaikan pembayaran.');
            }
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}

<?php

namespace App\Http\Responses;

use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    public function toResponse($request)
    {
        if ($user = $request->user()) {
            $invoice = app(OrderService::class)->handlePendingHostingOrder($user, $request);

            if ($invoice) {
                return $request->wantsJson()
                    ? new JsonResponse(['redirect' => route('checkout', $invoice->id)], 200)
                    : redirect()->route('checkout', $invoice->id)
                        ->with('success', 'Pesanan hosting berhasil dibuat. Silakan selesaikan pembayaran.');
            }
        }

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended(Fortify::redirects('login'));
    }
}

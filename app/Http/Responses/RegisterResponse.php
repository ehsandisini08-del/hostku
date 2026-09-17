<?php

namespace App\Http\Responses;

use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        if ($user = $request->user()) {
            $invoice = app(OrderService::class)->handlePendingHostingOrder($user, $request);

            if ($invoice) {
                return $request->wantsJson()
                    ? new JsonResponse(['redirect' => route('checkout', $invoice->id)], 201)
                    : redirect()->route('checkout', $invoice->id)
                        ->with('success', 'Akun berhasil dibuat! Silakan selesaikan pembayaran hosting Anda.');
            }
        }

        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->intended(Fortify::redirects('register'));
    }
}

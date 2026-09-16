<?php

namespace App\Providers;

use App\Services\Payment\DokuAdapter;
use App\Services\Payment\MidtransAdapter;
use App\Services\Payment\PaymentService;
use App\Services\Payment\XenditAdapter;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function () {
            $service = new PaymentService;

            if (config('services.midtrans.server_key')) {
                $service->register('midtrans', new MidtransAdapter(
                    serverKey: config('services.midtrans.server_key'),
                    clientKey: config('services.midtrans.client_key'),
                    merchantId: config('services.midtrans.merchant_id'),
                    isProduction: config('services.midtrans.is_production', false),
                ));
            }

            if (config('services.xendit.api_key')) {
                $service->register('xendit', new XenditAdapter(
                    apiKey: config('services.xendit.api_key'),
                    callbackToken: config('services.xendit.callback_token'),
                ));
            }

            if (config('services.doku.client_id')) {
                $service->register('doku', new DokuAdapter(
                    clientId: config('services.doku.client_id'),
                    secretKey: config('services.doku.secret_key'),
                ));
            }

            return $service;
        });
    }
}

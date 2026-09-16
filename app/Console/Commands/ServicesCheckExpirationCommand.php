<?php

namespace App\Console\Commands;

use App\Models\Service;
use Illuminate\Console\Command;

class ServicesCheckExpirationCommand extends Command
{
    protected $signature = 'services:check-expiration';

    protected $description = 'Check and update service expiration statuses';

    public function handle(): void
    {
        $expiring = Service::where('status', 'active')
            ->where('expired_at', '<=', now()->addDays(7))
            ->where('expired_at', '>', now())
            ->get();

        foreach ($expiring as $service) {
            $service->update(['status' => 'expiring']);
        }

        $this->info("Marked {$expiring->count()} services as expiring.");
    }
}

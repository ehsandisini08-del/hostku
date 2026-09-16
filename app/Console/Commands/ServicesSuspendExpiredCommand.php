<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\Setting;
use App\Notifications\ServiceSuspendedNotification;
use Illuminate\Console\Command;

class ServicesSuspendExpiredCommand extends Command
{
    protected $signature = 'services:suspend-expired';

    protected $description = 'Suspend services past their grace period';

    public function handle(): void
    {
        $graceDays = (int) (Setting::where('key', 'grace_period_days')->first()?->value ?? 3);

        $services = Service::where('status', 'expiring')
            ->where('expired_at', '<', now()->subDays($graceDays))
            ->get();

        foreach ($services as $service) {
            $service->update(['status' => 'suspended', 'suspended_at' => now()]);

            if ($service->user) {
                $service->user->notify(new ServiceSuspendedNotification($service));
            }
        }

        $this->info("Suspended {$services->count()} services.");
    }
}

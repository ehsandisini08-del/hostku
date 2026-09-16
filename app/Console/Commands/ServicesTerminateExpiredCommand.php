<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Notifications\ServiceTerminatedNotification;
use Illuminate\Console\Command;

class ServicesTerminateExpiredCommand extends Command
{
    protected $signature = 'services:terminate-expired';

    protected $description = 'Terminate services past their suspension period';

    public function handle(): void
    {
        $suspensionDays = 7;

        $services = Service::where('status', 'suspended')
            ->where('suspended_at', '<', now()->subDays($suspensionDays))
            ->get();

        foreach ($services as $service) {
            $service->update(['status' => 'terminated', 'terminated_at' => now()]);

            if ($service->user) {
                $service->user->notify(new ServiceTerminatedNotification($service));
            }
        }

        $this->info("Terminated {$services->count()} services.");
    }
}

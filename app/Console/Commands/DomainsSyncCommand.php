<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Services\Domain\DomainService;
use Illuminate\Console\Command;

class DomainsSyncCommand extends Command
{
    protected $signature = 'domains:sync';

    protected $description = 'Sync domain statuses from registrar';

    public function handle(DomainService $domainService): void
    {
        $domains = Domain::whereNotNull('registrar_id')->get();
        $count = 0;

        foreach ($domains as $domain) {
            $domainService->sync($domain);
            $count++;
        }

        $this->info("Synced {$count} domains.");
    }
}

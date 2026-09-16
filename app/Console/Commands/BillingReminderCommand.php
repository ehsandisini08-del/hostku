<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\ServiceExpiringNotification;
use Illuminate\Console\Command;

class BillingReminderCommand extends Command
{
    protected $signature = 'billing:reminder';

    protected $description = 'Send payment reminders for unpaid invoices';

    public function handle(): void
    {
        $reminderDays = [7, 3, 1];

        foreach ($reminderDays as $days) {
            $invoices = Invoice::with('user')
                ->where('status', 'pending')
                ->whereDate('due_date', now()->addDays($days)->toDateString())
                ->get();

            foreach ($invoices as $invoice) {
                if ($invoice->user) {
                    $invoice->user->notify(new ServiceExpiringNotification($invoice, $days));
                }
            }

            $this->info("Sent {$invoices->count()} H-{$days} reminders.");
        }
    }
}

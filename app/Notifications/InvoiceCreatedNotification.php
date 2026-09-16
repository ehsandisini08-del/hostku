<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formatPrice = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return (new MailMessage)
            ->subject("Invoice Baru: {$this->invoice->invoice_number}")
            ->line("Invoice baru sebesar {$formatPrice($this->invoice->total)} telah dibuat.")
            ->line("Jatuh tempo: {$this->invoice->due_date}")
            ->action('Lihat Invoice', url("/customer/invoices/{$this->invoice->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Invoice baru: {$this->invoice->invoice_number}",
            'invoice_number' => $this->invoice->invoice_number,
        ];
    }
}

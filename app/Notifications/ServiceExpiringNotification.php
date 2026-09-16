<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(private Invoice $invoice, private int $daysLeft) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formatPrice = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return (new MailMessage)
            ->subject("Layanan Anda akan expired dalam {$this->daysLeft} hari")
            ->line("Invoice #{$this->invoice->invoice_number} sebesar {$formatPrice($this->invoice->total)}")
            ->line("Jatuh tempo: {$this->invoice->due_date}")
            ->action('Bayar Sekarang', url("/customer/invoices/{$this->invoice->id}"))
            ->line('Segera lakukan pembayaran untuk menghindari suspensi layanan.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Invoice #{$this->invoice->invoice_number} akan jatuh tempo dalam {$this->daysLeft} hari.",
            'invoice_number' => $this->invoice->invoice_number,
            'days_left' => $this->daysLeft,
        ];
    }
}

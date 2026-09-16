<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceSuspendedNotification extends Notification
{
    use Queueable;

    public function __construct(private Service $service) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Layanan Anda telah disuspend')
            ->line("Layanan #{$this->service->id} telah disuspend karena melewati masa tenggang.")
            ->line('Segera lakukan pembayaran untuk mengaktifkan kembali layanan Anda.')
            ->action('Lihat Invoice', url('/customer/invoices'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Layanan #{$this->service->id} telah disuspend.",
            'service_id' => $this->service->id,
        ];
    }
}

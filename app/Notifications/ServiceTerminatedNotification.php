<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceTerminatedNotification extends Notification
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
            ->subject('Layanan Anda telah diterminasi')
            ->line("Layanan #{$this->service->id} telah diterminasi.")
            ->line('Semua data terkait layanan ini telah dihapus.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Layanan #{$this->service->id} telah diterminasi.",
            'service_id' => $this->service->id,
        ];
    }
}

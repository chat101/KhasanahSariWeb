<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PerintahProduksiUpdated extends Notification
{
    public function __construct(
        public ?string $title = null,
        public ?string $body  = null,
        public ?string $url   = null,
        public ?string $icon  = '/icons/icon-192.png',
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, $notification = null): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title ?: 'Perintah Produksi')
            ->icon($this->icon ?: '/icons/icon-192.png')
            ->body($this->body ?: 'Ada pembaruan perintah produksi.')
            ->data(['url' => $this->url ?: url('/produksi/work-order')]);
    }
}

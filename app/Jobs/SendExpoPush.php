<?php

namespace App\Jobs;

use App\Services\ExpoPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendExpoPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array  $tokens,
        public string $title,
        public string $body = '',
        public array  $data = [],
        public string $channelId = 'alerts',
        public string $priority  = 'high',
    ) {}

    public function handle(ExpoPushService $push): void
    {
        $tokens = array_values(array_unique(array_filter(
            $this->tokens,
            fn ($t) => is_string($t) && str_starts_with($t, 'ExponentPushToken[')
        )));
        if (!$tokens) return;

        $push->sendToMany(
            tokens:   $tokens,
            title:    $this->title,
            body:     $this->body,
            data:     $this->data,
            channelId:$this->channelId,
            priority: $this->priority
        );
    }
}

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

    /**
     * Queue parameters
     */
    public function __construct(
        public array  $tokens,
        public string $title,
        public string $body      = '',
        public array  $data      = [],
        public ?string $sound    = null,
        public string $channelId = 'alerts',
        public string $priority  = 'high',
        public int    $ttl       = 1800,
    ) {}

    /**
     * Handle queue job
     */
    public function handle(ExpoPushService $push): void
    {
            /** @var \App\Services\ExpoPushService $push */
        // Filter token yang valid saja
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
            sound:    $this->sound,
            channelId:$this->channelId,
            priority: $this->priority,
            ttl:      $this->ttl,
        );
    }
}

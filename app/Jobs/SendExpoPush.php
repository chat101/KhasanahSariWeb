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
        public array   $tokens,
        public string  $title,
        public string  $body      = '',
        public array   $data      = [],
        public ?string $sound     = null,

        // 🔥 Default WO channel (bukan alerts)
        public string  $channelId = 'work_order_alerts',

        public string  $priority  = 'high',
        public int     $ttl       = 1800,
    ) {}

    public function handle(ExpoPushService $expo): void
    {
        /** @var \App\Services\ExpoPushService $expo */

        // Filter token valid
        $tokens = array_values(array_unique(array_filter(
            $this->tokens,
            fn ($t) => is_string($t) && str_starts_with($t, 'ExponentPushToken[')
        )));

        if (!$tokens) return;

        // kirim push
        $expo->sendToMany(
            tokens:    $tokens,
            title:     $this->title,
            body:      $this->body,
            data:      $this->data,
            sound:     $this->sound,
            channelId: $this->channelId, // <---- ambil dari constructor
            priority:  $this->priority,
            ttl:       $this->ttl
        );
    }
}

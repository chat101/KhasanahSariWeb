<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    private function blockNonTicketForTeknisi(array $data): bool
    {
        return isset($data['role'])
            && $data['role'] === 'teknisi'
            && (!isset($data['type']) || $data['type'] !== 'ticket_new');
    }

    public function send(
        string $to,
        string $title,
        ?string $body = '',
        array $data = [],
        ?string $sound = null,

        // 🔥 Default WO channel
        string $channelId = 'work_order_alerts',

        string $priority = 'high',
        int $ttl = 1800
    ): array {

        if ($this->blockNonTicketForTeknisi($data)) {
            return ['skipped' => true];
        }

        $payload = [
            'to'        => $to,
            'title'     => $title,
            'body'      => $body ?? '',
            'data'      => $data,
            'channelId' => $channelId,
            'priority'  => $priority,
            'ttl'       => $ttl,
        ];

        if (!empty($sound)) {
            $payload['sound'] = $sound;
        }

        return Http::acceptJson()->asJson()
            ->post('https://exp.host/--/api/v2/push/send', $payload)
            ->throw()
            ->json();
    }

    public function sendToMany(
        array $tokens,
        string $title,
        ?string $body = '',
        array $data = [],
        ?string $sound = null,

        // 🔥 Default WO channel
        string $channelId = 'work_order_alerts',

        string $priority = 'high',
        int $ttl = 1800
    ): array {

        if ($this->blockNonTicketForTeknisi($data)) {
            return ['skipped' => true];
        }

        $messages = array_map(function ($to)
            use ($title, $body, $data, $sound, $channelId, $priority, $ttl) {

            $msg = [
                'to'        => $to,
                'title'     => $title,
                'body'      => $body ?? '',
                'data'      => $data,
                'channelId' => $channelId,
                'priority'  => $priority,
                'ttl'       => $ttl,
            ];

            if (!empty($sound)) {
                $msg['sound'] = $sound;
            }

            return $msg;
        }, $tokens);

        $tickets = [];

        foreach (array_chunk($messages, 100) as $chunk) {
            try {
                $res = Http::acceptJson()->asJson()
                    ->post('https://exp.host/--/api/v2/push/send', $chunk)
                    ->throw()
                    ->json();

                $tickets = array_merge($tickets, $res['data'] ?? []);

            } catch (\Throwable $e) {
                Log::error("expo push error chunk", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $tickets;
    }
}

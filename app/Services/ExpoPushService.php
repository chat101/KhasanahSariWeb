<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    /**
     * @param array|string[]|Collection $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     */

        public function send(string $to, string $title, ?string $body = '', array $data = [], string $channelId='alerts', string $priority='high'): array
        {
            $payload = [
                'to'        => $to,
                'title'     => $title,
                'body'      => $body ?? '',
                'data'      => $data,
                'channelId' => $channelId,
                'priority'  => $priority,
            ];

            return Http::acceptJson()->asJson()
                ->post('https://exp.host/--/api/v2/push/send', $payload)
                ->throw()
                ->json();
        }

        public function sendToMany(array $tokens, string $title, ?string $body = '', array $data = [], string $channelId='alerts', string $priority='high'): array
        {
            $messages = array_map(fn($to) => [
                'to'        => $to,
                'title'     => $title,
                'body'      => $body ?? '',
                'data'      => $data,
                'channelId' => $channelId,
                'priority'  => $priority,
            ], $tokens);

            $tickets = [];
            foreach (array_chunk($messages, 100) as $chunk) {
                $res = Http::acceptJson()->asJson()
                    ->post('https://exp.host/--/api/v2/push/send', $chunk)
                    ->throw()
                    ->json();
                $tickets = array_merge($tickets, $res['data'] ?? []);
            }
            return $tickets;
        }

}

<?php

namespace App\Infrastructure\EventBus\Adapters;

use App\Infrastructure\EventBus\Events\MessagePublished;
use Illuminate\Support\Facades\Log;

class LocalAdapter
{
    public function publish(string $topic, array $payload): void
    {
        event(new MessagePublished($topic, $payload));

        Log::channel('stack')->debug('Local event dispatched', [
            'topic' => $topic,
            'payload' => $payload,
        ]);
    }
}


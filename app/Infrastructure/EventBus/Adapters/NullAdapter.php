<?php

namespace App\Infrastructure\EventBus\Adapters;

use App\Infrastructure\EventBus\Contracts\RemoteEventAdapter;
use Illuminate\Support\Facades\Log;

class NullAdapter implements RemoteEventAdapter
{
    public function publish(string $topic, array $payload): void
    {
        Log::debug('Null remote adapter skipped publish', [
            'topic' => $topic,
        ]);
    }
}


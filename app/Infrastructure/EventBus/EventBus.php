<?php

namespace App\Infrastructure\EventBus;

use App\Infrastructure\EventBus\Adapters\LocalAdapter;
use App\Infrastructure\EventBus\Contracts\RemoteEventAdapter;

class EventBus
{
    public function __construct(
        protected LocalAdapter $localAdapter,
        protected RemoteEventAdapter $remoteAdapter,
    ) {
    }

    public function publish(string $topic, array $payload): void
    {
        $this->localAdapter->publish($topic, $payload);
        $this->remoteAdapter->publish($topic, $payload);
    }
}


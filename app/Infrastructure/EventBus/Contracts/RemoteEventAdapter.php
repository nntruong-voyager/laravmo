<?php

namespace App\Infrastructure\EventBus\Contracts;

interface RemoteEventAdapter
{
    public function publish(string $topic, array $payload): void;
}


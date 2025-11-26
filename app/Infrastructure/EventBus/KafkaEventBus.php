<?php

namespace App\Infrastructure\EventBus;

use App\Infrastructure\EventBus\Adapters\KafkaAdapter;
use App\Infrastructure\EventBus\Adapters\LocalAdapter;

class KafkaEventBus extends EventBus
{
    public function __construct(LocalAdapter $localAdapter, KafkaAdapter $remoteAdapter)
    {
        parent::__construct($localAdapter, $remoteAdapter);
    }
}


<?php

namespace App\Infrastructure\EventBus\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagePublished
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $topic,
        public readonly array $payload,
    ) {
    }
}


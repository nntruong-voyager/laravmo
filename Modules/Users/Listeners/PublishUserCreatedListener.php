<?php

namespace Modules\Users\Listeners;

use App\Infrastructure\EventBus\EventBus;
use Shared\Events\UserCreated;

class PublishUserCreatedListener
{
    public function __construct(private readonly EventBus $bus)
    {
    }

    public function handle(UserCreated $event): void
    {
        $this->bus->publish($event->topic(), $event->payload());
    }
}


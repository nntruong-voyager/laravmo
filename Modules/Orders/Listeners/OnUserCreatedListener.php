<?php

namespace Modules\Orders\Listeners;

use Shared\Contracts\V1\OrderServiceInterface;
use Shared\Events\UserCreated;

class OnUserCreatedListener
{
    public function __construct(private readonly OrderServiceInterface $orders)
    {
    }

    public function handle(UserCreated $event): void
    {
        $this->orders->createFromUser($event->user);
    }
}

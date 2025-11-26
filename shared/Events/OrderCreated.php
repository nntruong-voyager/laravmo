<?php

namespace Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shared\DTOs\V1\OrderDTO;

class OrderCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly OrderDTO $order)
    {
    }

    public function topic(): string
    {
        return config('eventbus.topics.order_created');
    }

    public function payload(): array
    {
        return $this->order->toArray();
    }
}

<?php

namespace Modules\Payments\Listeners;

use Shared\Contracts\V1\PaymentServiceInterface;
use Shared\DTOs\V1\PaymentDTO;
use Shared\Events\OrderCreated;

class OnOrderCreatedListener
{
    public function __construct(private readonly PaymentServiceInterface $payments)
    {
    }

    public function handle(OrderCreated $event): void
    {
        $payload = $event->order->toArray();

        $this->payments->process(new PaymentDTO(
            id: null,
            orderId: $payload['id'],
            amount: (float) ($payload['total'] ?? 0),
            status: 'pending',
        ));
    }
}

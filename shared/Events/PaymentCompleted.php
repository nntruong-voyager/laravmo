<?php

namespace Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shared\DTOs\V1\PaymentDTO;

class PaymentCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly PaymentDTO $payment)
    {
    }

    public function topic(): string
    {
        return config('eventbus.topics.payment_completed');
    }

    public function payload(): array
    {
        return $this->payment->toArray();
    }
}

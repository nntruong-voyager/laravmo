<?php

namespace Shared\DTOs\V1;

use Illuminate\Contracts\Support\Arrayable;
use Modules\Payments\Models\Payment;

class PaymentDTO implements Arrayable
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $orderId,
        public readonly float $amount,
        public readonly string $status = 'pending',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['order_id'],
            (float) $data['amount'],
            $data['status'] ?? 'pending',
        );
    }

    public static function fromModel(Payment $payment): self
    {
        return new self(
            $payment->id,
            $payment->order_id,
            (float) $payment->amount,
            $payment->status,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'status' => $this->status,
        ];
    }
}


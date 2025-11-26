<?php

namespace Shared\DTOs\V1;

use Illuminate\Contracts\Support\Arrayable;
use Modules\Orders\Models\Order;

class OrderDTO implements Arrayable
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $productSku,
        public readonly int $quantity,
        public readonly string $status = 'pending',
        public readonly ?float $total = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['user_id'],
            $data['product_sku'],
            $data['quantity'],
            $data['status'] ?? 'pending',
            $data['total'] ?? null,
        );
    }

    public static function fromModel(Order $order): self
    {
        return new self(
            $order->id,
            $order->user_id,
            $order->product_sku,
            $order->quantity,
            $order->status,
            $order->total,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'product_sku' => $this->productSku,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'total' => $this->total,
        ];
    }
}


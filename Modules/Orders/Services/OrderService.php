<?php

namespace Modules\Orders\Services;

use App\Infrastructure\ServiceLocator\ServiceLocator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Models\Order;
use Shared\Contracts\V1\InventoryServiceInterface;
use Shared\Contracts\V1\OrderServiceInterface;
use Shared\DTOs\V1\OrderDTO;
use Shared\DTOs\V1\UserDTO;
use Shared\Events\OrderCreated;

class OrderService implements OrderServiceInterface
{
    private readonly InventoryServiceInterface $inventory;

    public function __construct()
    {
        // Use Service Locator for cross-module communication
        // In monolith: resolves to local service
        // In microservices: resolves to HTTP adapter
        $this->inventory = ServiceLocator::make()->resolve(InventoryServiceInterface::class);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()->latest()->paginate($perPage);
    }

    public function create(OrderDTO $data): Order
    {
        return DB::transaction(function () use ($data) {
            $product = $this->inventory->reserve($data->productSku, $data->quantity);

            $order = Order::create([
                'user_id' => $data->userId,
                'product_sku' => $product->sku,
                'quantity' => $data->quantity,
                'status' => $data->status,
                'total' => $product->price * $data->quantity,
            ]);

            event(new OrderCreated(OrderDTO::fromModel($order)));

            return $order;
        });
    }

    public function createFromUser(UserDTO $user): Order
    {
        $product = $this->inventory->all()->first();

        if ($product === null) {
            throw new \RuntimeException('No products available to seed welcome order.');
        }

        $dto = new OrderDTO(
            id: null,
            userId: $user->id,
            productSku: $product->sku,
            quantity: 1,
            status: 'pending',
            total: $product->price,
        );

        return $this->create($dto);
    }

    public function markAsPaid(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);
        $order->update(['status' => 'paid']);
    }

    public function find(int $id): Order
    {
        return Order::query()->findOrFail($id);
    }
}

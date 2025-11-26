<?php

namespace Shared\Contracts\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Orders\Models\Order;
use Shared\DTOs\V1\OrderDTO;
use Shared\DTOs\V1\UserDTO;

interface OrderServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(OrderDTO $data): Order;

    public function createFromUser(UserDTO $user): Order;

    public function markAsPaid(int $orderId): void;

    public function find(int $id): Order;
}


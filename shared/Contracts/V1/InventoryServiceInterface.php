<?php

namespace Shared\Contracts\V1;

use Illuminate\Support\Collection;
use Modules\Inventory\Models\Product;

interface InventoryServiceInterface
{
    public function all(): Collection;

    public function findBySku(string $sku): ?Product;

    public function reserve(string $sku, int $quantity): Product;

    public function release(string $sku, int $quantity): Product;
}


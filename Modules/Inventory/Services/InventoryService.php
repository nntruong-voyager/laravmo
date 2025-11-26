<?php

namespace Modules\Inventory\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Product;
use Shared\Contracts\V1\InventoryServiceInterface;

class InventoryService implements InventoryServiceInterface
{
    public function all(): Collection
    {
        return Product::query()->orderBy('name')->get();
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::query()->where('sku', $sku)->first();
    }

    public function reserve(string $sku, int $quantity): Product
    {
        return DB::transaction(function () use ($sku, $quantity) {
            $product = Product::query()->where('sku', $sku)->lockForUpdate()->firstOrFail();

            if ($product->stock < $quantity) {
                throw new \RuntimeException('Insufficient stock for '.$sku);
            }

            $product->decrement('stock', $quantity);

            return $product->refresh();
        });
    }

    public function release(string $sku, int $quantity): Product
    {
        return DB::transaction(function () use ($sku, $quantity) {
            $product = Product::query()->where('sku', $sku)->lockForUpdate()->firstOrFail();
            $product->increment('stock', $quantity);

            return $product->refresh();
        });
    }
}

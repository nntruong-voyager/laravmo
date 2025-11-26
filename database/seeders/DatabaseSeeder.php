<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Inventory\Models\Product;
use Modules\Orders\Models\Order;
use Modules\Payments\Models\Payment;
use Modules\Users\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ],
        );

        $products = collect([
            ['sku' => 'SKU-100', 'name' => 'Premium Plan', 'price' => 99.00, 'stock' => 100],
            ['sku' => 'SKU-200', 'name' => 'Enterprise Plan', 'price' => 499.00, 'stock' => 50],
        ]);

        $products->each(fn ($product) => Product::query()->updateOrCreate(
            ['sku' => $product['sku']],
            $product,
        ));

        $order = Order::query()->create([
            'user_id' => $user->id,
            'product_sku' => 'SKU-100',
            'quantity' => 1,
            'status' => 'paid',
            'total' => 99.00,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'amount' => 99.00,
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }
}

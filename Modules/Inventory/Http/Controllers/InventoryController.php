<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Shared\Contracts\V1\InventoryServiceInterface;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryServiceInterface $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->all());
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'sku' => ['required', 'unique:products,sku'],
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::create($payload);

        return response()->json($product, 201);
    }
}

<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shared\Contracts\V1\OrderServiceInterface;
use Shared\DTOs\V1\OrderDTO;

class OrderController extends Controller
{
    public function __construct(private readonly OrderServiceInterface $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'product_sku' => ['required', 'exists:products,sku'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = $this->service->create(OrderDTO::fromArray($payload));

        return response()->json($order, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->find($id));
    }
}

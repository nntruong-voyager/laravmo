<?php

namespace Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shared\Contracts\V1\PaymentServiceInterface;
use Shared\DTOs\V1\PaymentDTO;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentServiceInterface $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $payment = $this->service->process(PaymentDTO::fromArray([
            ...$payload,
            'status' => 'pending',
        ]));

        return response()->json($payment, 201);
    }
}

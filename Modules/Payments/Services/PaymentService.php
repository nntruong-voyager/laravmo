<?php

namespace Modules\Payments\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Payments\Models\Payment;
use Shared\Contracts\V1\OrderServiceInterface;
use Shared\Contracts\V1\PaymentServiceInterface;
use Shared\DTOs\V1\PaymentDTO;
use Shared\Events\PaymentCompleted;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(private readonly OrderServiceInterface $orders)
    {
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Payment::query()->latest()->paginate($perPage);
    }

    public function process(PaymentDTO $data): Payment
    {
        $payment = Payment::create([
            'order_id' => $data->orderId,
            'amount' => $data->amount,
            'status' => $data->status,
        ]);

        return $this->complete($payment);
    }

    public function complete(Payment $payment): Payment
    {
        $payment->fill([
            'status' => 'completed',
            'paid_at' => now(),
        ])->save();

        $this->orders->markAsPaid($payment->order_id);

        event(new PaymentCompleted(PaymentDTO::fromModel($payment)));

        return $payment;
    }
}

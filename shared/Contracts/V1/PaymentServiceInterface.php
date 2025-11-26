<?php

namespace Shared\Contracts\V1;

use Modules\Payments\Models\Payment;
use Shared\DTOs\V1\PaymentDTO;

interface PaymentServiceInterface
{
    public function process(PaymentDTO $data): Payment;

    public function complete(Payment $payment): Payment;
}


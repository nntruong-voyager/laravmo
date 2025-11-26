<?php

namespace Shared\Contracts;

use Shared\Contracts\V1\PaymentServiceInterface as V1PaymentServiceInterface;

/**
 * Alias to V1\PaymentServiceInterface for backward compatibility.
 * 
 * @deprecated Use Shared\Contracts\V1\PaymentServiceInterface instead.
 * @see \Shared\Contracts\V1\PaymentServiceInterface
 */
interface PaymentServiceInterface extends V1PaymentServiceInterface
{
}

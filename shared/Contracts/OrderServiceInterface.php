<?php

namespace Shared\Contracts;

use Shared\Contracts\V1\OrderServiceInterface as V1OrderServiceInterface;

/**
 * Alias to V1\OrderServiceInterface for backward compatibility.
 * 
 * @deprecated Use Shared\Contracts\V1\OrderServiceInterface instead.
 * @see \Shared\Contracts\V1\OrderServiceInterface
 */
interface OrderServiceInterface extends V1OrderServiceInterface
{
}

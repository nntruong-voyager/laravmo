<?php

namespace Shared\Contracts;

use Shared\Contracts\V1\InventoryServiceInterface as V1InventoryServiceInterface;

/**
 * Alias to V1\InventoryServiceInterface for backward compatibility.
 * 
 * @deprecated Use Shared\Contracts\V1\InventoryServiceInterface instead.
 * @see \Shared\Contracts\V1\InventoryServiceInterface
 */
interface InventoryServiceInterface extends V1InventoryServiceInterface
{
}

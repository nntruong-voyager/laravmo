<?php

namespace Shared\Contracts;

use Shared\Contracts\V1\UserServiceInterface as V1UserServiceInterface;

/**
 * Alias to V1\UserServiceInterface for backward compatibility.
 * 
 * @deprecated Use Shared\Contracts\V1\UserServiceInterface instead.
 * @see \Shared\Contracts\V1\UserServiceInterface
 */
interface UserServiceInterface extends V1UserServiceInterface
{
}

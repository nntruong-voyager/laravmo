<?php

namespace App\Infrastructure\ServiceLocator\Adapters;

use App\Infrastructure\ServiceLocator\Contracts\ServiceLocatorInterface;
use Illuminate\Contracts\Container\Container;

/**
 * Local Service Locator - resolves services from the same container.
 * Used in monolithic architecture.
 */
class LocalServiceLocator implements ServiceLocatorInterface
{
    public function __construct(
        private readonly Container $container
    ) {
    }

    public function resolve(string $contract): object
    {
        if (!$this->container->bound($contract)) {
            throw new \RuntimeException("Service contract {$contract} is not bound in container.");
        }

        return $this->container->make($contract);
    }
}


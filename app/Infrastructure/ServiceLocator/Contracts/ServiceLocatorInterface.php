<?php

namespace App\Infrastructure\ServiceLocator\Contracts;

/**
 * Service Locator Interface for Cross-Module Communication
 *
 * This abstraction allows modules to communicate without direct dependencies.
 * In a monolith, it resolves to local services. In microservices, it can
 * route to HTTP/gRPC adapters.
 */
interface ServiceLocatorInterface
{
    /**
     * Resolve a service by its interface/contract name.
     *
     * @param string $contract The fully qualified interface name
     * @return object The service implementation
     * @throws \RuntimeException If service cannot be resolved
     */
    public function resolve(string $contract): object;
}


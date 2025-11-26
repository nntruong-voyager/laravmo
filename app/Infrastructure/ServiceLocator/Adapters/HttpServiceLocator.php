<?php

namespace App\Infrastructure\ServiceLocator\Adapters;

use App\Infrastructure\ServiceLocator\Contracts\ServiceLocatorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Shared\Contracts\V1\InventoryServiceInterface;
use Shared\Contracts\V1\OrderServiceInterface;
use Shared\Contracts\V1\PaymentServiceInterface;
use Shared\Contracts\V1\UserServiceInterface;

/**
 * HTTP Service Locator - resolves services via HTTP calls.
 * Used when modules are extracted into microservices.
 *
 * Configuration:
 * - SERVICE_USERS_URL=http://users-service:8000
 * - SERVICE_ORDERS_URL=http://orders-service:8000
 * - etc.
 */
class HttpServiceLocator implements ServiceLocatorInterface
{
    private array $serviceMap = [
        UserServiceInterface::class => 'users',
        OrderServiceInterface::class => 'orders',
        PaymentServiceInterface::class => 'payments',
        InventoryServiceInterface::class => 'inventory',
        // Backward compatibility aliases
        \Shared\Contracts\UserServiceInterface::class => 'users',
        \Shared\Contracts\OrderServiceInterface::class => 'orders',
        \Shared\Contracts\PaymentServiceInterface::class => 'payments',
        \Shared\Contracts\InventoryServiceInterface::class => 'inventory',
    ];

    public function resolve(string $contract): object
    {
        if (!isset($this->serviceMap[$contract])) {
            throw new \RuntimeException("No HTTP service mapping for contract {$contract}");
        }

        $serviceName = $this->serviceMap[$contract];
        $baseUrl = config("services.{$serviceName}.url", "http://{$serviceName}-service:8000");

        return new class($baseUrl, $contract) {
            public function __construct(
                private readonly string $baseUrl,
                private readonly string $contract
            ) {
            }

            public function __call(string $method, array $arguments)
            {
                $response = Http::timeout(5)
                    ->post("{$this->baseUrl}/api/{$method}", [
                        'arguments' => $arguments,
                    ]);

                if (!$response->successful()) {
                    Log::error("HTTP service call failed", [
                        'service' => $this->baseUrl,
                        'method' => $method,
                        'status' => $response->status(),
                    ]);

                    throw new \RuntimeException("Service call to {$this->baseUrl}/{$method} failed.");
                }

                return $response->json();
            }
        };
    }
}

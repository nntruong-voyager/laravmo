<?php

namespace App\Infrastructure\ServiceLocator\Adapters;

use App\Infrastructure\ServiceLocator\Contracts\ServiceLocatorInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Shared\Contracts\V1\InventoryServiceInterface;
use Shared\Contracts\V1\OrderServiceInterface;
use Shared\Contracts\V1\PaymentServiceInterface;
use Shared\Contracts\V1\UserServiceInterface;

/**
 * Hybrid Service Locator - resolves services based on per-service configuration.
 * Used when some modules are extracted while others remain in the monolith.
 *
 * Configuration (config/services.php or .env):
 * - SERVICE_USERS_MODE=local|http
 * - SERVICE_USERS_URL=http://users-service:8000
 * - etc.
 */
class HybridServiceLocator implements ServiceLocatorInterface
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

    public function __construct(
        private readonly Container $container
    ) {
    }

    public function resolve(string $contract): object
    {
        if (!isset($this->serviceMap[$contract])) {
            throw new \RuntimeException("No service mapping for contract {$contract}");
        }

        $serviceName = $this->serviceMap[$contract];
        $mode = config("services.{$serviceName}.mode", 'local');

        return match ($mode) {
            'http' => $this->resolveHttp($contract, $serviceName),
            default => $this->resolveLocal($contract),
        };
    }

    /**
     * Resolve service from local container (monolith mode)
     */
    private function resolveLocal(string $contract): object
    {
        if (!$this->container->bound($contract)) {
            throw new \RuntimeException("Service contract {$contract} is not bound in container.");
        }

        return $this->container->make($contract);
    }

    /**
     * Resolve service via HTTP calls (microservice mode)
     */
    private function resolveHttp(string $contract, string $serviceName): object
    {
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

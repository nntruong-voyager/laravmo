<?php

namespace App\Infrastructure\ServiceLocator;

use App\Infrastructure\ServiceLocator\Adapters\HttpServiceLocator;
use App\Infrastructure\ServiceLocator\Adapters\HybridServiceLocator;
use App\Infrastructure\ServiceLocator\Adapters\LocalServiceLocator;
use App\Infrastructure\ServiceLocator\Contracts\ServiceLocatorInterface;

/**
 * Service Locator Factory
 *
 * Determines which adapter to use based on environment configuration.
 * - LOCAL mode: uses LocalServiceLocator (monolith)
 * - HTTP mode: uses HttpServiceLocator (full microservices)
 * - HYBRID mode: uses HybridServiceLocator (partial extraction)
 */
class ServiceLocator
{
    public static function make(): ServiceLocatorInterface
    {
        $mode = config('services.locator.mode', env('SERVICE_LOCATOR_MODE', 'local'));

        return match ($mode) {
            'http', 'microservices' => new HttpServiceLocator(),
            'hybrid' => new HybridServiceLocator(app()),
            default => new LocalServiceLocator(app()),
        };
    }
}

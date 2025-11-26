<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service Locator Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how cross-module communication works.
    |
    | Global Mode (SERVICE_LOCATOR_MODE):
    | - 'local': All services resolved from same container (monolith)
    | - 'http': All services resolved via HTTP calls (full microservices)
    | - 'hybrid': Per-service configuration (partial extraction)
    |
    | When using 'hybrid' mode, each service can be configured individually
    | with its own 'mode' setting.
    |
    */

    'locator' => [
        'mode' => env('SERVICE_LOCATOR_MODE', 'local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Microservice Configuration
    |--------------------------------------------------------------------------
    |
    | Configure each service individually.
    |
    | For each service:
    | - 'mode': 'local' (same container) or 'http' (remote service)
    | - 'url': Base URL when mode is 'http'
    |
    | Environment variables:
    | - SERVICE_{NAME}_MODE: Override mode per service
    | - SERVICE_{NAME}_URL: Override URL per service
    |
    */

    'users' => [
        'mode' => env('SERVICE_USERS_MODE', 'local'),
        'url' => env('SERVICE_USERS_URL', 'http://users-service:8000'),
    ],

    'orders' => [
        'mode' => env('SERVICE_ORDERS_MODE', 'local'),
        'url' => env('SERVICE_ORDERS_URL', 'http://orders-service:8000'),
    ],

    'payments' => [
        'mode' => env('SERVICE_PAYMENTS_MODE', 'local'),
        'url' => env('SERVICE_PAYMENTS_URL', 'http://payments-service:8000'),
    ],

    'inventory' => [
        'mode' => env('SERVICE_INVENTORY_MODE', 'local'),
        'url' => env('SERVICE_INVENTORY_URL', 'http://inventory-service:8000'),
    ],

];

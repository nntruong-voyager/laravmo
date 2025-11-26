<?php

namespace App\Providers;

use App\Infrastructure\EventBus\Adapters\KafkaAdapter;
use App\Infrastructure\EventBus\Adapters\LocalAdapter;
use App\Infrastructure\EventBus\Adapters\NullAdapter;
use App\Infrastructure\EventBus\EventBus;
use App\Infrastructure\EventBus\KafkaEventBus;
use Illuminate\Support\ServiceProvider;

class EventBusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LocalAdapter::class, static fn () => new LocalAdapter());

        $this->app->singleton(KafkaAdapter::class, static function () {
            $config = config('eventbus');

            return new KafkaAdapter($config['brokers'], $config['client_id']);
        });

        $this->app->singleton(EventBus::class, function ($app) {
            $mode = config('eventbus.mode', 'kafka');

            if ($mode === 'kafka') {
                return new KafkaEventBus(
                    $app->make(LocalAdapter::class),
                    $app->make(KafkaAdapter::class),
                );
            }

            return new EventBus(
                $app->make(LocalAdapter::class),
                $app->make(NullAdapter::class),
            );
        });

        $this->app->singleton(NullAdapter::class, static fn () => new NullAdapter());
    }
}


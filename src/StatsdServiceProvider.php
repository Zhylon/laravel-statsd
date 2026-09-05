<?php

namespace Zhylon\LaravelStatsd;

use Illuminate\Support\ServiceProvider;
use Zhylon\LaravelStatsd\Contracts\StatsdClient;

class StatsdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statsd.php', 'statsd');

        $this->app->singleton(StatsdClient::class, function () {
            $shouldFire = config('statsd.enabled') && ! $this->app->runningUnitTests();

            return $shouldFire
                ? new UdpStatsdClient(
                    config('statsd.host'),
                    (int) config('statsd.port'),
                    config('statsd.prefix', ''),
                    (float) config('statsd.timeout', 0.1),
                )
                : new NullStatsdClient;
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/statsd.php' => config_path('statsd.php'),
        ], 'config');
    }
}

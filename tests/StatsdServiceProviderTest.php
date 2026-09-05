<?php

use Zhylon\LaravelStatsd\UdpStatsdClient;
use Zhylon\LaravelStatsd\NullStatsdClient;
use Zhylon\LaravelStatsd\Contracts\StatsdClient;

it('merges the default config', function () {
    expect(config('statsd.host'))->toBe('127.0.0.1')
        ->and(config('statsd.port'))->toBe(8125)
        ->and(config('statsd.prefix'))->toBe('')
        ->and(config('statsd.enabled'))->toBeTrue();
});

it('binds the null client while running unit tests, regardless of the enabled flag', function () {
    config()->set('statsd.enabled', true);

    expect($this->app->make(StatsdClient::class))->toBeInstanceOf(NullStatsdClient::class);
});

it('binds the udp client outside of unit tests when enabled', function () {
    config()->set('statsd.enabled', true);
    $this->app['env'] = 'production';

    expect($this->app->make(StatsdClient::class))->toBeInstanceOf(UdpStatsdClient::class);
});

it('binds the null client outside of unit tests when disabled', function () {
    config()->set('statsd.enabled', false);
    $this->app['env'] = 'production';

    expect($this->app->make(StatsdClient::class))->toBeInstanceOf(NullStatsdClient::class);
});

it('resolves the same singleton instance on repeated resolutions', function () {
    $client = $this->app->make(StatsdClient::class);

    expect($this->app->make(StatsdClient::class))->toBe($client);
});

it('publishes the config file', function () {
    $this->artisan('vendor:publish', ['--tag' => 'config'])->run();

    expect(file_exists(config_path('statsd.php')))->toBeTrue();

    @unlink(config_path('statsd.php'));
});

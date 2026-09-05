<?php

use Zhylon\LaravelStatsd\Facades\Statsd;
use Zhylon\LaravelStatsd\NullStatsdClient;
use Zhylon\LaravelStatsd\Contracts\StatsdClient;

it('resolves to the same instance bound in the container', function () {
    expect(Statsd::getFacadeRoot())->toBe($this->app->make(StatsdClient::class));
});

it('proxies calls to the underlying client without throwing', function () {
    expect(Statsd::getFacadeRoot())->toBeInstanceOf(NullStatsdClient::class);

    expect(fn () => Statsd::increment('jobs.processed'))->not->toThrow(Throwable::class);
});

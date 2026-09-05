<?php

namespace Zhylon\LaravelStatsd\Tests;

use Zhylon\LaravelStatsd\Facades\Statsd;
use Orchestra\Testbench\TestCase as Orchestra;
use Zhylon\LaravelStatsd\StatsdServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            StatsdServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Statsd' => Statsd::class,
        ];
    }
}

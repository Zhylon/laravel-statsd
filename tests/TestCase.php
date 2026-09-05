<?php

namespace Zhylon\LaravelStatsd\Tests;

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
}

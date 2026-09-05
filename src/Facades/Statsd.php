<?php

namespace Zhylon\LaravelStatsd\Facades;

use Illuminate\Support\Facades\Facade;
use Zhylon\LaravelStatsd\Contracts\StatsdClient;

/**
 * @method static void increment(string $metric, int $value = 1, float $sampleRate = 1.0)
 * @method static void decrement(string $metric, int $value = 1, float $sampleRate = 1.0)
 * @method static void timing(string $metric, float $milliseconds, float $sampleRate = 1.0)
 * @method static void gauge(string $metric, int|float $value)
 * @method static void set(string $metric, int|string $value)
 */
class Statsd extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return StatsdClient::class;
    }
}

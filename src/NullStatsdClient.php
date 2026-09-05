<?php

namespace Zhylon\LaravelStatsd;

use Zhylon\LaravelStatsd\Contracts\StatsdClient;

class NullStatsdClient implements StatsdClient
{
    public function increment(string $metric, int $value = 1, float $sampleRate = 1.0): void
    {
        //
    }

    public function decrement(string $metric, int $value = 1, float $sampleRate = 1.0): void
    {
        //
    }

    public function timing(string $metric, float $milliseconds, float $sampleRate = 1.0): void
    {
        //
    }

    public function gauge(string $metric, int|float $value): void
    {
        //
    }

    public function set(string $metric, int|string $value): void
    {
        //
    }
}

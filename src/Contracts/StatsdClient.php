<?php

namespace Zhylon\LaravelStatsd\Contracts;

/**
 * The four StatsD metric types (https://github.com/statsd/statsd/wiki/Client-Implementations,
 * https://github.com/statsd/statsd/blob/master/docs/metric_types.md), sent
 * over the standard wire protocol "metric:value|type[|@rate]".
 */
interface StatsdClient
{
    /**
     * Increment a counter ("c") by the given value.
     */
    public function increment(string $metric, int $value = 1, float $sampleRate = 1.0): void;

    /**
     * Decrement a counter ("c") by the given value.
     */
    public function decrement(string $metric, int $value = 1, float $sampleRate = 1.0): void;

    /**
     * Record a duration in milliseconds ("ms").
     */
    public function timing(string $metric, float $milliseconds, float $sampleRate = 1.0): void;

    /**
     * Record an absolute measurement ("g").
     */
    public function gauge(string $metric, int|float $value): void;

    /**
     * Count the number of unique values seen for a metric ("s").
     */
    public function set(string $metric, int|string $value): void;
}

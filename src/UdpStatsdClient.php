<?php

namespace Zhylon\LaravelStatsd;

use Zhylon\LaravelStatsd\Contracts\StatsdClient;

/**
 * Sends metrics to any standard StatsD server (https://github.com/statsd/statsd)
 * over UDP using the plain wire protocol "metric:value|type[|@rate]" — no
 * vendor-specific extensions. Fire-and-forget by design: a dropped packet or
 * an unreachable server must never delay or fail the work being measured,
 * so every failure mode here is swallowed silently.
 */
class UdpStatsdClient implements StatsdClient
{
    /** @var resource|false|null */
    protected $socket;

    public function __construct(
        protected string $host,
        protected int $port,
        protected string $prefix = '',
    ) {}

    public function increment(string $metric, int $value = 1, float $sampleRate = 1.0): void
    {
        $this->send($metric, (string) $value, 'c', $sampleRate);
    }

    public function decrement(string $metric, int $value = 1, float $sampleRate = 1.0): void
    {
        $this->increment($metric, -$value, $sampleRate);
    }

    public function timing(string $metric, float $milliseconds, float $sampleRate = 1.0): void
    {
        $this->send($metric, (string) $milliseconds, 'ms', $sampleRate);
    }

    public function gauge(string $metric, int|float $value): void
    {
        $this->send($metric, (string) $value, 'g');
    }

    public function set(string $metric, int|string $value): void
    {
        $this->send($metric, (string) $value, 's');
    }

    protected function send(string $metric, string $value, string $type, float $sampleRate = 1.0): void
    {
        if ($sampleRate < 1.0 && (mt_rand() / mt_getrandmax()) > $sampleRate) {
            return;
        }

        $name = '' !== $this->prefix ? $this->prefix.'.'.$metric : $metric;
        $suffix = $sampleRate < 1.0 ? '|@'.$sampleRate : '';
        $payload = $name.':'.$value.'|'.$type.$suffix;

        $socket = $this->getSocket();

        if (! $socket) {
            return;
        }

        @fwrite($socket, $payload);
    }

    /**
     * @return resource|false
     */
    protected function getSocket()
    {
        if ($this->socket) {
            return $this->socket;
        }

        return $this->socket = @fsockopen('udp://'.$this->host, $this->port, $errno, $errstr, 0.1);
    }
}

<?php

use Zhylon\LaravelStatsd\UdpStatsdClient;

/**
 * Binds an ephemeral UDP listener, runs $send against its port, and returns
 * every datagram it received within the timeout — exercising the real wire
 * protocol instead of mocking the socket.
 *
 * @return array<int, string>
 */
function captureUdpPayloads(callable $send, float $timeout = 0.2): array
{
    $server = stream_socket_server('udp://127.0.0.1:0', $errno, $errstr, STREAM_SERVER_BIND);
    $address = stream_socket_get_name($server, false);
    $port = (int) substr($address, strrpos($address, ':') + 1);

    $send($port);

    $payloads = [];

    while (true) {
        $read = [$server];
        $write = $except = [];

        if (stream_select($read, $write, $except, 0, (int) ($timeout * 1_000_000)) <= 0) {
            break;
        }

        $payload = stream_socket_recvfrom($server, 1024);

        if (false === $payload || '' === $payload) {
            break;
        }

        $payloads[] = $payload;
    }

    fclose($server);

    return $payloads;
}

function captureUdpPayload(callable $send): ?string
{
    return captureUdpPayloads($send)[0] ?? null;
}

it('sends an increment as a counter with a default value of 1', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port))->increment('jobs.processed'));

    expect($payload)->toBe('jobs.processed:1|c');
});

it('sends an increment with a custom value', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port))->increment('jobs.processed', 5));

    expect($payload)->toBe('jobs.processed:5|c');
});

it('sends a decrement as a negative counter', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port))->decrement('jobs.queued', 3));

    expect($payload)->toBe('jobs.queued:-3|c');
});

it('sends a timing in milliseconds', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port))->timing('request.duration', 12.5));

    expect($payload)->toBe('request.duration:12.5|ms');
});

it('sends a gauge as an absolute measurement', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port))->gauge('queue.size', 42));

    expect($payload)->toBe('queue.size:42|g');
});

it('sends a set to count unique values', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port))->set('users.active', 'user-123'));

    expect($payload)->toBe('users.active:user-123|s');
});

it('prefixes every metric name when a prefix is configured', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port, 'myapp'))->increment('jobs.processed'));

    expect($payload)->toBe('myapp.jobs.processed:1|c');
});

it('omits the sample rate suffix at the default rate of 1.0', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port))->increment('jobs.processed', 1, 1.0));

    expect($payload)->toBe('jobs.processed:1|c');
});

it('never sends a metric sampled at a rate of 0', function () {
    $payloads = captureUdpPayloads(function (int $port) {
        $client = new UdpStatsdClient('127.0.0.1', $port);

        for ($i = 0; $i < 20; $i++) {
            $client->increment('jobs.processed', 1, 0.0);
        }
    });

    expect($payloads)->toBe([]);
});

it('appends the sample rate suffix when a metric is actually sent below rate 1.0', function () {
    $payload = captureUdpPayload(fn (int $port) => (new UdpStatsdClient('127.0.0.1', $port))->increment('jobs.processed', 1, 0.999_999_999));

    expect($payload)->toBe('jobs.processed:1|c|@0.999999999');
});

it('never fails when nothing is listening on the target port', function () {
    $client = new UdpStatsdClient('127.0.0.1', 1);

    expect(fn () => $client->increment('jobs.processed'))->not->toThrow(Throwable::class);
});

it('defaults the socket timeout to 0.1 seconds', function () {
    $client = new UdpStatsdClient('127.0.0.1', 8125);

    $property = new ReflectionProperty($client, 'timeout');
    $property->setAccessible(true);

    expect($property->getValue($client))->toBe(0.1);
});

it('accepts a custom socket timeout', function () {
    $client = new UdpStatsdClient('127.0.0.1', 8125, timeout: 2.5);

    $property = new ReflectionProperty($client, 'timeout');
    $property->setAccessible(true);

    expect($property->getValue($client))->toBe(2.5);
});

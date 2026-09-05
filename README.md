# laravel-statsd

[![Latest Stable Version](http://poser.pugx.org/zhylon/laravel-statsd/v)](https://packagist.org/packages/zhylon/laravel-statsd)
[![Tests](https://github.com/Zhylon/laravel-statsd/actions/workflows/tests.yml/badge.svg)](https://github.com/Zhylon/laravel-statsd/actions/workflows/tests.yml)
[![License](http://poser.pugx.org/zhylon/laravel-statsd/license)](https://packagist.org/packages/zhylon/laravel-statsd)

Fire-and-forget StatsD metrics for Laravel, built for [Netdata's built-in StatsD collector](https://learn.netdata.cloud/docs/collecting-metrics/statsd) but compatible with any standard [StatsD](https://github.com/statsd/statsd) server.

Metrics are sent over UDP: a dropped packet or an unreachable server never delays or fails the request being measured.

## Requirements

- PHP 8.2+
- Laravel 12.x or 13.x

## Installation

```bash
composer require zhylon/laravel-statsd
```

Publish the config file:

```bash
php artisan vendor:publish --tag=config
```

Point it at your StatsD server in `.env`:

```
STATSD_HOST=127.0.0.1
STATSD_PORT=8125
STATSD_PREFIX=myapp
```

## Usage

Type-hint the `StatsdClient` contract wherever you need it — it's bound as a singleton in the container:

```php
use Zhylon\LaravelStatsd\Contracts\StatsdClient;

class ProcessesOrders
{
    public function __construct(private StatsdClient $statsd) {}

    public function handle(Order $order): void
    {
        $this->statsd->increment('orders.processed');

        $start = microtime(true);
        // ... do the work ...
        $this->statsd->timing('orders.duration', (microtime(true) - $start) * 1000);

        $this->statsd->gauge('orders.queue_size', Order::pending()->count());
        $this->statsd->set('orders.unique_customers', $order->customer_id);
    }
}
```

Or use the `Statsd` facade wherever constructor injection is inconvenient:

```php
use Zhylon\LaravelStatsd\Facades\Statsd;

Statsd::increment('orders.processed');
```

| Method                                                                 | Metric type   | Description                             |
|------------------------------------------------------------------------|---------------|-----------------------------------------|
| `increment(string $metric, int $value = 1, float $sampleRate = 1.0)`   | Counter (`c`) | Increments a counter by `$value`        |
| `decrement(string $metric, int $value = 1, float $sampleRate = 1.0)`   | Counter (`c`) | Decrements a counter by `$value`        |
| `timing(string $metric, float $milliseconds, float $sampleRate = 1.0)` | Timing (`ms`) | Records a duration in milliseconds      |
| `gauge(string $metric, int\|float $value)`                             | Gauge (`g`)   | Records an absolute measurement         |
| `set(string $metric, int\|string $value)`                              | Set (`s`)     | Counts the number of unique values seen |

`$sampleRate` (between `0.0` and `1.0`) lets high-volume counters and timings sample instead of firing on every call — the client appends the `|@rate` suffix so your StatsD server scales the value back up.

### Automatic no-op during tests

The package binds a `NullStatsdClient` automatically whenever `$app->runningUnitTests()` is true, so your test suite never fires real UDP packets — no need to fake or mock the client yourself.

### Config

| Key       | Env variable     | Default     | Description                                                   |
|-----------|------------------|-------------|---------------------------------------------------------------|
| `enabled` | `STATSD_ENABLED` | `true`      | Master switch. When `false`, the null client is bound.        |
| `host`    | `STATSD_HOST`    | `127.0.0.1` | UDP host of the StatsD server                                 |
| `port`    | `STATSD_PORT`    | `8125`      | UDP port of the StatsD server                                 |
| `timeout` | `STATSD_TIMEOUT` | `0.1`       | Socket timeout in seconds for opening the UDP connection      |
| `prefix`  | `STATSD_PREFIX`  | `''`        | Prepended to every metric name, e.g. `myapp.orders.processed` |

## Testing

```bash
composer test
```

## License

The MIT License (MIT). See [LICENSE](LICENSE) for more information.

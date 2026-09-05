<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch for the package. When disabled — or during automated
    | tests, see StatsdServiceProvider — the null client is bound instead,
    | so no metrics are ever sent over the wire.
    */
    'enabled' => (bool) env('STATSD_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Server
    |--------------------------------------------------------------------------
    |
    | UDP host/port of the StatsD server to send metrics to, e.g. Netdata's
    | built-in StatsD collector, which listens on 8125 by default.
    */
    'host' => env('STATSD_HOST', '127.0.0.1'),
    'port' => (int) env('STATSD_PORT', 8125),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Socket timeout in seconds for opening the UDP connection. This is not
    | a per-packet send timeout — UDP writes don't block — it only bounds
    | how long fsockopen() may take, so it can stay very low.
    */
    'timeout' => (float) env('STATSD_TIMEOUT', 0.1),

    /*
    |--------------------------------------------------------------------------
    | Prefix
    |--------------------------------------------------------------------------
    |
    | Prepended to every metric name, e.g. "myapp.checks.uptime". Empty by
    | default, per the plain StatsD wire protocol.
    */
    'prefix' => env('STATSD_PREFIX', ''),

];

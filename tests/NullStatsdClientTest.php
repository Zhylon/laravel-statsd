<?php

use Zhylon\LaravelStatsd\NullStatsdClient;

it('silently no-ops every metric call', function () {
    $client = new NullStatsdClient;

    expect($client->increment('foo'))->toBeNull()
        ->and($client->decrement('foo'))->toBeNull()
        ->and($client->timing('foo', 12.3))->toBeNull()
        ->and($client->gauge('foo', 42))->toBeNull()
        ->and($client->set('foo', 'unique-value'))->toBeNull();
});

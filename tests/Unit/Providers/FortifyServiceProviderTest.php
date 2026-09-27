<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\RateLimiter;

it('limits passkey attempts by credential id and ip', function (): void {
    $request = Request::create('/passkeys/login', 'POST', [
        'credential' => ['id' => 'credential-id'],
    ], server: ['REMOTE_ADDR' => '127.0.0.1']);

    $limiter = RateLimiter::limiter('passkeys');

    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe(10)
        ->and($limit->key)->toBe('credential-id|127.0.0.1');
});

it('limits passkey attempts by session id and ip without credential id', function (): void {
    $session = new Store('test', new ArraySessionHandler(1));

    $request = Request::create('/passkeys/login/options', 'GET', server: ['REMOTE_ADDR' => '127.0.0.1']);
    $request->setLaravelSession($session);

    $limiter = RateLimiter::limiter('passkeys');

    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe(10)
        ->and($limit->key)->toBe($session->getId().'|127.0.0.1');
});

<?php

declare(strict_types=1);

use App\Http\Middleware\HandleAppearance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

it('shares appearance cookie value with views', function (): void {
    $middleware = new HandleAppearance();

    $request = Request::create('/', 'GET');
    $request->cookies->set('appearance', 'dark');

    $response = $middleware->handle($request, fn ($req): Response => response('OK'));

    expect(View::shared('appearance'))->toBe('dark')
        ->and($response->getContent())->toBe('OK');
});

it('defaults to system when appearance cookie not present', function (): void {
    $middleware = new HandleAppearance();

    $request = Request::create('/', 'GET');

    $response = $middleware->handle($request, fn ($req): Response => response('OK'));

    expect(View::shared('appearance'))->toBe('system')
        ->and($response->getContent())->toBe('OK');
});

it('handles light appearance', function (): void {
    $middleware = new HandleAppearance();

    $request = Request::create('/', 'GET');
    $request->cookies->set('appearance', 'light');

    $middleware->handle($request, fn ($req): Response => response('OK'));

    expect(View::shared('appearance'))->toBe('light');
});

it('handles system appearance', function (): void {
    $middleware = new HandleAppearance();

    $request = Request::create('/', 'GET');
    $request->cookies->set('appearance', 'system');

    $middleware->handle($request, fn ($req): Response => response('OK'));

    expect(View::shared('appearance'))->toBe('system');
});

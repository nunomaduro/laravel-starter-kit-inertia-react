<?php

declare(strict_types=1);

use App\Http\Controllers\InstallController;
use App\Providers\SettingsOverlayServiceProvider;
use App\Settings\TenancySettings;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $path = base_path('.env');
    $this->originalEnv = file_exists($path) ? (string) file_get_contents($path) : '';
});

afterEach(function (): void {
    file_put_contents(base_path('.env'), $this->originalEnv);
});

it('applies tenancy and demo options on express install', function (): void {
    if (config('database.default') === 'sqlite' && (config('database.connections.sqlite.database') === ':memory:')) {
        $this->markTestSkipped('SQLite :memory: cannot VACUUM inside a transaction when express callback runs migrate.');
    }

    $dbPath = database_path('database.sqlite');
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }

    $this->withoutMiddleware([ValidateCsrfToken::class, ThrottleRequests::class]);

    $response = $this->postJson(route('install.express'), [
        'tenancy' => 'single',
        'demo' => 'none',
        'single_org_name' => 'Acme Corp',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['progressFile']);

    // Run terminating callbacks (express install runs in app()->terminating()).
    $kernel = $this->app->make(KernelContract::class);
    $kernel->terminate(request(), $response->baseResponse);

    // Express writes to database/database.sqlite; point default connection at it for assertions
    config(['database.default' => 'sqlite']);
    config(['database.connections.sqlite.database' => $dbPath]);
    DB::purge('sqlite');
    SettingsOverlayServiceProvider::applyOverlay();

    $tenancy = resolve(TenancySettings::class);
    expect($tenancy->enabled)->toBeFalse()
        ->and($tenancy->allow_user_org_creation)->toBeFalse()
        ->and($tenancy->default_org_name)->toBe('Acme Corp');
});

it('resolves preset to tenancy and demo when preset is internal', function (): void {
    $request = Request::create('/install/express', 'POST', ['preset' => 'internal']);
    $controller = new InstallController;
    $method = new ReflectionMethod(InstallController::class, 'resolveExpressOptions');
    $method->setAccessible(true);
    $options = $method->invoke($controller, $request);

    expect($options)->toHaveKeys(['tenancy', 'demo'])
        ->and($options['tenancy'])->toBe('single')
        ->and($options['demo'])->toBe('none');
});

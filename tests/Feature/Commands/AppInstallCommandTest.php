<?php

declare(strict_types=1);

use App\Settings\AppSettings;
use App\Settings\BroadcastingSettings;
use App\Settings\FilesystemSettings;
use App\Settings\MailSettings;
use App\Settings\PrismSettings;
use App\Settings\ScoutSettings;
use App\Settings\SetupWizardSettings;
use App\Settings\TenancySettings;
use Illuminate\Support\Facades\Artisan;

// Snapshot and restore .env so tests that write env vars never leave the
// project in a broken state (e.g. CACHE_STORE=redis with no Redis server).
beforeEach(function (): void {
    $path = base_path('.env');
    $this->originalEnv = file_exists($path) ? (string) file_get_contents($path) : '';

    // Seed scout settings so ScoutSettings can be resolved without TypeError
    // (the settings migration data rows are not present in the schema dump).
    foreach ([
        'driver' => 'collection',
        'prefix' => '',
        'queue' => false,
        'identify' => false,
        'typesense_api_key' => null,
        'typesense_host' => 'localhost',
        'typesense_port' => 8108,
        'typesense_protocol' => 'http',
    ] as $name => $value) {
        Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['group' => 'scout', 'name' => $name],
            [
                'locked' => false,
                'payload' => json_encode($value),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
});

afterEach(function (): void {
    // Restore .env file to prevent subsequent tests inheriting e.g. CACHE_STORE=redis.
    file_put_contents(base_path('.env'), $this->originalEnv);

    // Reset Settings that could bleed into the next test via applyOverlay().
    // Use the Settings API (not raw DB) so the Spatie settings cache is also flushed.
    try {
        resolve(ScoutSettings::class)->fill(['driver' => 'collection'])->save();
        resolve(FilesystemSettings::class)->fill(['default_disk' => 'local'])->save();
        resolve(BroadcastingSettings::class)->fill([
            'default_connection' => 'log',
            'reverb_app_id' => null,
            'reverb_app_key' => null,
            'reverb_app_secret' => null,
        ])->save();
        resolve(PrismSettings::class)->fill(['default_provider' => 'openrouter'])->save();
        resolve(TenancySettings::class)->fill(['enabled' => true])->save();
    } catch (Throwable) {
        // Settings table might not exist in very first test before migrations run.
    }

    // Reset runtime configs mutated by applyOverlay() so they don't bleed into
    // subsequent tests sharing this process (Scout, filesystem, cache, etc.).
    config([
        'scout.driver' => 'null',
        'filesystems.default' => 'local',
        'cache.default' => 'array',
        'queue.default' => 'sync',
        'session.driver' => 'array',
    ]);

    // Force Scout to re-resolve its engine so Typesense config doesn't persist.
    resolve(Laravel\Scout\EngineManager::class)->forgetEngines();
});

// ─── Core installer behaviour ─────────────────────────────────────────────────

it('marks setup complete when run with non-interactive', function (): void {
    $wizard = resolve(SetupWizardSettings::class);
    expect($wizard->setup_completed)->toBeFalse();

    $exitCode = Artisan::call('app:install', ['--non-interactive' => true]);

    expect($exitCode)->toBe(0);

    $wizard = resolve(SetupWizardSettings::class);
    expect($wizard->setup_completed)->toBeTrue()
        ->and($wizard->completed_steps)->toBe(['app', 'mail', 'billing', 'ai', 'complete']);
});

it('is idempotent with non-interactive when setup already complete', function (): void {
    $wizard = resolve(SetupWizardSettings::class);
    $wizard->setup_completed = true;
    $wizard->completed_steps = ['app', 'mail', 'billing', 'ai', 'complete'];
    $wizard->save();

    $exitCode = Artisan::call('app:install', ['--non-interactive' => true]);

    expect($exitCode)->toBe(0);

    $wizard = resolve(SetupWizardSettings::class);
    expect($wizard->setup_completed)->toBeTrue();
});

it('persists app and mail settings with non-interactive', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--site-name' => 'Test Site',
        '--url' => 'https://test.example.com',
        '--mail-from' => 'noreply@test.example',
        '--mail-from-name' => 'Test App',
    ]);

    $app = resolve(AppSettings::class);
    expect($app->site_name)->toBe('Test Site')
        ->and($app->url)->toBe('https://test.example.com');

    $mail = resolve(MailSettings::class);
    expect($mail->from_address)->toBe('noreply@test.example')
        ->and($mail->from_name)->toBe('Test App');
});

// ─── New CI/CD flags ──────────────────────────────────────────────────────────

it('configures single-tenant mode via --tenancy=single flag', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--tenancy' => 'single',
        '--org-name' => 'Acme Corp',
    ]);

    $tenancy = resolve(TenancySettings::class);
    expect($tenancy->enabled)->toBeFalse()
        ->and($tenancy->default_org_name)->toBe('Acme Corp');
});

it('configures multi-tenant mode via --tenancy=multi flag', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--tenancy' => 'multi',
    ]);

    $tenancy = resolve(TenancySettings::class);
    expect($tenancy->enabled)->toBeTrue();
});

it('configures AI provider and API key via CI flags', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--ai-provider' => 'openai',
        '--ai-api-key' => 'sk-test-key',
    ]);

    $prism = resolve(PrismSettings::class);
    expect($prism->default_provider)->toBe('openai')
        ->and($prism->openai_api_key)->toBe('sk-test-key');
});

it('configures search driver=collection via CI flag', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--search-driver' => 'collection',
    ]);

    $scout = resolve(ScoutSettings::class);
    expect($scout->driver)->toBe('collection');
});

it('writes redis env vars to .env when --cache-driver=redis is passed', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--cache-driver' => 'redis',
        '--redis-host' => '10.0.0.1',
        '--redis-port' => '6380',
    ]);

    $writtenEnv = (string) file_get_contents(base_path('.env'));

    expect($writtenEnv)
        ->toContain('CACHE_STORE=redis')
        ->toContain('SESSION_DRIVER=redis')
        ->toContain('QUEUE_CONNECTION=redis')
        ->toContain('REDIS_HOST=10.0.0.1')
        ->toContain('REDIS_PORT=6380');
});

it('writes typesense env vars when --search-driver=typesense is passed', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--search-driver' => 'typesense',
        '--typesense-key' => 'ts-test-key',
        '--typesense-host' => 'ts.example.com',
    ]);

    $writtenEnv = (string) file_get_contents(base_path('.env'));

    expect($writtenEnv)
        ->toContain('TYPESENSE_API_KEY=ts-test-key')
        ->toContain('TYPESENSE_HOST=ts.example.com');
});

it('configures s3 storage via CI flags', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--s3-disk' => 's3',
        '--s3-key' => 'AKIATEST',
        '--s3-secret' => 'secret123',
        '--s3-region' => 'eu-west-1',
        '--s3-bucket' => 'my-bucket',
    ]);

    $fs = resolve(FilesystemSettings::class);
    expect($fs->default_disk)->toBe('s3')
        ->and($fs->s3_key)->toBe('AKIATEST')
        ->and($fs->s3_bucket)->toBe('my-bucket')
        ->and($fs->s3_region)->toBe('eu-west-1');
});

it('configures reverb broadcasting via CI flags', function (): void {
    Artisan::call('app:install', [
        '--non-interactive' => true,
        '--reverb-app-id' => 'app-123',
        '--reverb-app-key' => 'key-abc',
        '--reverb-app-secret' => 'secret-xyz',
    ]);

    $bc = resolve(BroadcastingSettings::class);
    expect($bc->reverb_app_id)->toBe('app-123')
        ->and($bc->reverb_app_key)->toBe('key-abc');
});

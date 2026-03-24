<?php

declare(strict_types=1);

use App\Support\ModuleFeatureRegistry;
use App\Support\ModuleLoader;
use App\Support\ModuleNavigationRegistry;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    ModuleFeatureRegistry::flush();
    ModuleNavigationRegistry::flush();
});

/*
|--------------------------------------------------------------------------
| Module enable / disable via config
|--------------------------------------------------------------------------
*/

it('disables module when config is false', function (): void {
    config(['modules.contact' => false]);

    $provider = new Modules\Contact\Providers\ContactModuleServiceProvider(app());

    expect($provider->isEnabled())->toBeFalse();
});

it('reports enabled when config is true', function (): void {
    config(['modules.contact' => true]);

    $provider = new Modules\Contact\Providers\ContactModuleServiceProvider(app());

    expect($provider->isEnabled())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| ModuleFeatureRegistry — registration of module features
|--------------------------------------------------------------------------
*/

it('registers module features in ModuleFeatureRegistry when module is enabled', function (): void {
    config(['modules.contact' => true]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    $features = ModuleFeatureRegistry::moduleInertiaFeatures();

    expect($features)->toHaveKey('contact');
});

it('does not register features in ModuleFeatureRegistry when module is disabled', function (): void {
    config(['modules.contact' => false]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    $features = ModuleFeatureRegistry::moduleInertiaFeatures();

    expect($features)->not->toHaveKey('contact');
});

/*
|--------------------------------------------------------------------------
| Module nav items shared via Inertia
|--------------------------------------------------------------------------
*/

it('shares module nav items via Inertia', function (): void {
    $user = createTestUser(['onboarding_completed' => true]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('moduleNavItems'));
});

/*
|--------------------------------------------------------------------------
| ModuleNavigationRegistry — collects navigation items
|--------------------------------------------------------------------------
*/

it('collects navigation items from modules', function (): void {
    // Re-register the blog module (it has navigation) to ensure at least one group exists
    config(['modules.blog' => true]);
    $provider = new Modules\Blog\Providers\BlogModuleServiceProvider(app());
    $provider->register();
    $provider->boot();

    $groups = ModuleNavigationRegistry::allGroups();

    expect($groups)->not->toBeEmpty();
});

it('returns empty groups when no modules are booted', function (): void {
    // Registry already flushed in beforeEach
    $groups = ModuleNavigationRegistry::allGroups();

    expect($groups)->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| make:module command — scaffolds complete module structure
|--------------------------------------------------------------------------
*/

it('scaffolds a complete module structure', function (): void {
    $this->artisan('make:module', ['name' => 'TestScaffold', '--no-interaction' => true])
        ->assertSuccessful();

    $modulePath = base_path('modules/test-scaffold');

    expect(is_dir($modulePath))->toBeTrue()
        ->and(file_exists($modulePath.'/module.json'))->toBeTrue()
        ->and(file_exists($modulePath.'/src/Providers/TestScaffoldModuleServiceProvider.php'))->toBeTrue()
        ->and(file_exists($modulePath.'/src/Models/TestScaffold.php'))->toBeTrue();

    // Cleanup: remove generated directory
    File::deleteDirectory($modulePath);

    // Cleanup: remove entry added to config/modules.php
    $allModules = ModuleLoader::all();
    unset($allModules['test-scaffold']);
    ModuleLoader::writeConfig($allModules);

    // Cleanup: remove PSR-4 entry from composer.json
    $composerPath = base_path('composer.json');
    $composer = json_decode(File::get($composerPath), true);
    if (is_array($composer)) {
        unset($composer['autoload']['psr-4']['Modules\\TestScaffold\\']);
        File::put($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
    }
});

/*
|--------------------------------------------------------------------------
| ModuleLoader — discovers enabled module providers
|--------------------------------------------------------------------------
*/

it('discovers all enabled module providers', function (): void {
    $providers = ModuleLoader::providers();

    expect($providers)->not->toBeEmpty();
});

it('excludes disabled modules from provider list', function (): void {
    // Disable contact for this test
    $original = config('modules.contact');
    config(['modules.contact' => false]);

    $providers = ModuleLoader::providers();

    expect($providers)->not->toContain(Modules\Contact\Providers\ContactModuleServiceProvider::class);

    // Restore original value
    config(['modules.contact' => $original]);
});

it('returns only enabled providers from ModuleLoader', function (): void {
    // Ensure contact is enabled
    config(['modules.contact' => true]);

    $providers = ModuleLoader::providers();

    expect($providers)->toContain(Modules\Contact\Providers\ContactModuleServiceProvider::class);
});

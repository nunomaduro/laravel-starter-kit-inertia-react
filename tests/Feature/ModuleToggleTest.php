<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\ModuleFeatureRegistry;
use Laravel\Pennant\Feature;
use Modules\Contact\Features\ContactFeature;

beforeEach(function (): void {
    ModuleFeatureRegistry::flush();
});

/*
|--------------------------------------------------------------------------
| Contact module ENABLED
|--------------------------------------------------------------------------
*/

test('enabled contact module routes return 200', function (): void {
    // Contact is enabled by default — routes registered at boot.
    // Re-register features after flush so feature middleware resolves.
    config(['modules.contact' => true]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    $this->get('/contact')
        ->assertOk();
});

test('enabled module registers features in ModuleFeatureRegistry', function (): void {
    config(['modules.contact' => true]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    $inertiaFeatures = ModuleFeatureRegistry::allInertiaFeatures();

    expect($inertiaFeatures)->toHaveKey('contact')
        ->and($inertiaFeatures['contact'])->toBe(ContactFeature::class);
});

test('enabled module feature resolves as active for user', function (): void {
    config(['modules.contact' => true]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    $user = User::withoutEvents(fn (): User => User::factory()->create());

    // ContactFeature defaults to active — verify it resolves as active.
    expect(Feature::for($user)->active(ContactFeature::class))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Contact module DISABLED — service provider contract
|--------------------------------------------------------------------------
*/

test('disabled module service provider does not register features', function (): void {
    config(['modules.contact' => false]);

    $provider = new Modules\Contact\Providers\ContactModuleServiceProvider(app());
    $provider->register();
    $provider->boot();

    expect(ModuleFeatureRegistry::moduleInertiaFeatures())->not->toHaveKey('contact')
        ->and(ModuleFeatureRegistry::moduleRouteFeatures())->not->toHaveKey('contact');
});

test('disabled module isEnabled returns false', function (): void {
    config(['modules.contact' => false]);

    $provider = new Modules\Contact\Providers\ContactModuleServiceProvider(app());

    expect($provider->isEnabled())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Contact module DISABLED — route returns 404 via feature middleware
|--------------------------------------------------------------------------
|
| When the module is truly disabled, routes are never registered (natural 404).
| In tests the app boots with all modules enabled, so we simulate the disabled
| state by deactivating the Pennant feature flag — the `feature:contact`
| middleware on the route then returns 404, matching production behaviour.
|
*/

test('disabled contact feature causes route to return 404', function (): void {
    config(['modules.contact' => true]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    $user = User::withoutEvents(fn (): User => User::factory()->create());
    Feature::for($user)->deactivate(ContactFeature::class);

    $this->actingAs($user)
        ->get('/contact')
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Contact module DISABLED — features NOT in Inertia shared props
|--------------------------------------------------------------------------
|
| HandleInertiaRequests reads from ModuleFeatureRegistry::allInertiaFeatures().
| When the module is disabled, its feature is not registered, so it is absent
| from the shared features array sent to the frontend.
|
*/

test('disabled contact module features are absent from the registry', function (): void {
    // Registry flushed in beforeEach — contact not registered.
    // Only register non-contact modules.
    config(['modules.contact' => false]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    $allFeatures = ModuleFeatureRegistry::allInertiaFeatures();

    expect($allFeatures)->not->toHaveKey('contact');
});

/*
|--------------------------------------------------------------------------
| Enabled vs disabled contract in a single test
|--------------------------------------------------------------------------
*/

test('module toggle contract: enabled registers features, disabled does not', function (): void {
    // --- Enabled ---
    config(['modules.contact' => true]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    expect(ModuleFeatureRegistry::moduleInertiaFeatures())->toHaveKey('contact')
        ->and(ModuleFeatureRegistry::moduleRouteFeatures())->toHaveKey('contact');

    // --- Reset and test disabled ---
    ModuleFeatureRegistry::flush();
    config(['modules.contact' => false]);
    (new Modules\Contact\Providers\ContactModuleServiceProvider(app()))->register();

    expect(ModuleFeatureRegistry::moduleInertiaFeatures())->not->toHaveKey('contact')
        ->and(ModuleFeatureRegistry::moduleRouteFeatures())->not->toHaveKey('contact');
});

<?php

declare(strict_types=1);

use App\Actions\CreateOrganizationAction;
use App\Services\Organization\OrganizationRoleService;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->user = createTestUser();
    $this->organization = resolve(CreateOrganizationAction::class)->handle($this->user, 'Test Org');
    resolve(OrganizationRoleService::class)->syncRolePermissions($this->organization);
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();

    TenantContext::set($this->organization);
    setPermissionsTeamId(null);

    $this->withoutMiddleware(App\Http\Middleware\HandleInertiaRequests::class);
});

afterEach(function (): void {
    TenantContext::flush();
    setPermissionsTeamId(0);
});

it('rejects unauthenticated requests to features show', function (): void {
    TenantContext::flush();

    $response = $this->get(route('settings.features.show'));

    $response->assertRedirect(route('login'));
});

it('renders the features settings page for authenticated owner', function (): void {
    $response = $this->actingAs($this->user)->get(route('settings.features.show'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/features')
            ->has('features')
        );
});

it('rejects feature update with invalid override value', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.features.update'), [
        'key' => 'some_feature',
        'override' => 'invalid_value',
    ]);

    $response->assertSessionHasErrors(['override']);
});

it('rejects feature update with missing key', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.features.update'), [
        'override' => 'enabled',
    ]);

    $response->assertSessionHasErrors(['key']);
});

it('rejects feature update for non-member user', function (): void {
    $otherUser = createTestUser(['email' => 'other@example.com']);

    $response = $this->actingAs($otherUser)->post(route('settings.features.update'), [
        'key' => 'some_feature',
        'override' => 'enabled',
    ]);

    $response->assertForbidden();
});

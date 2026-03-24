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

    // HandleInertiaRequests::share() checks hasRole('super-admin') under team 0,
    // caching the roles relation and poisoning the later PermissionMiddleware check.
    $this->withoutMiddleware(App\Http\Middleware\HandleInertiaRequests::class);
});

afterEach(function (): void {
    TenantContext::flush();
    setPermissionsTeamId(0);
});

it('rejects unauthenticated requests to settings general show', function (): void {
    TenantContext::flush();

    $response = $this->get(route('settings.general.show'));

    $response->assertRedirect(route('login'));
});

it('renders the general settings page for authenticated owner', function (): void {
    $response = $this->actingAs($this->user)->get(route('settings.general.show'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/general')
            ->has('organization')
            ->has('baseDomain')
        );
});

it('rejects slug update with invalid data', function (): void {
    $response = $this->actingAs($this->user)->patch(route('settings.general.slug.update'), [
        'slug' => '',
        'confirmed' => false,
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('rejects slug update without confirmation', function (): void {
    $response = $this->actingAs($this->user)->patch(route('settings.general.slug.update'), [
        'slug' => 'valid-new-slug',
        'confirmed' => false,
    ]);

    $response->assertSessionHasErrors(['confirmed']);
});

it('updates the organization slug with valid data', function (): void {
    $response = $this->actingAs($this->user)->patch(route('settings.general.slug.update'), [
        'slug' => 'new-valid-slug',
        'confirmed' => true,
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $this->organization->refresh();
    expect($this->organization->slug)->toBe('new-valid-slug');
});

it('returns success when slug is unchanged', function (): void {
    $currentSlug = $this->organization->slug;

    $response = $this->actingAs($this->user)->patch(route('settings.general.slug.update'), [
        'slug' => $currentSlug,
        'confirmed' => true,
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success', 'No changes made.');
});

it('rejects slug update for non-member user', function (): void {
    $otherUser = createTestUser(['email' => 'other@example.com']);

    $response = $this->actingAs($otherUser)->patch(route('settings.general.slug.update'), [
        'slug' => 'stolen-slug',
        'confirmed' => true,
    ]);

    $response->assertForbidden();
});

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

it('rejects unauthenticated requests to roles index', function (): void {
    TenantContext::flush();

    $response = $this->get(route('settings.roles.index'));

    $response->assertRedirect(route('login'));
});

it('renders the roles settings page for authenticated owner', function (): void {
    $response = $this->actingAs($this->user)->get(route('settings.roles.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/roles')
            ->has('customRoles')
        );
});

it('rejects role store with invalid data', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.roles.store'), [
        'name' => '',
        'label' => '',
        'permissions' => 'not-an-array',
    ]);

    $response->assertSessionHasErrors(['name', 'label', 'permissions']);
});

it('rejects role store with name exceeding max length', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.roles.store'), [
        'name' => str_repeat('a', 65),
        'label' => 'Test Role',
        'permissions' => ['org.members.view'],
    ]);

    $response->assertSessionHasErrors(['name']);
});

it('rejects role store with non-alpha-dash name', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.roles.store'), [
        'name' => 'invalid name with spaces',
        'label' => 'Test Role',
        'permissions' => ['org.members.view'],
    ]);

    $response->assertSessionHasErrors(['name']);
});

it('stores a custom role with valid data', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.roles.store'), [
        'name' => 'custom-viewer',
        'label' => 'Custom Viewer',
        'permissions' => ['org.members.view'],
    ]);

    $response->assertRedirect()
        ->assertSessionHas('flash');
});

it('rejects role store for non-member user', function (): void {
    $otherUser = createTestUser(['email' => 'other@example.com']);

    $response = $this->actingAs($otherUser)->post(route('settings.roles.store'), [
        'name' => 'custom-role',
        'label' => 'Custom Role',
        'permissions' => ['org.members.view'],
    ]);

    $response->assertForbidden();
});

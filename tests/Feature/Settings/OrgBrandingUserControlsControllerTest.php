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

it('rejects unauthenticated requests to branding user controls', function (): void {
    TenantContext::flush();

    $response = $this->post(route('settings.branding.user-controls'));

    $response->assertRedirect(route('login'));
});

it('rejects branding user controls with invalid data', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.branding.user-controls'), [
        'user_can_change_colors' => 'not-boolean',
    ]);

    $response->assertSessionHasErrors(['user_can_change_colors']);
});

it('rejects branding user controls with missing fields', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.branding.user-controls'), [
        'user_can_change_colors' => true,
        // missing other required fields
    ]);

    $response->assertSessionHasErrors([
        'user_can_change_font',
        'user_can_change_layout',
        'user_can_change_logo',
    ]);
});

it('updates branding user controls with valid data', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.branding.user-controls'), [
        'user_can_change_colors' => true,
        'user_can_change_font' => false,
        'user_can_change_layout' => true,
        'user_can_change_logo' => false,
    ]);

    $response->assertRedirect()
        ->assertSessionHas('flash');
});

it('rejects branding user controls for non-member user', function (): void {
    $otherUser = createTestUser(['email' => 'other@example.com']);

    $response = $this->actingAs($otherUser)->post(route('settings.branding.user-controls'), [
        'user_can_change_colors' => true,
        'user_can_change_font' => false,
        'user_can_change_layout' => true,
        'user_can_change_logo' => false,
    ]);

    $response->assertForbidden();
});

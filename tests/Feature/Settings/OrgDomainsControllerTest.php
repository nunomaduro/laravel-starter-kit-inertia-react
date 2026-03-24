<?php

declare(strict_types=1);

use App\Actions\CreateOrganizationAction;
use App\Models\OrganizationDomain;
use App\Services\Organization\OrganizationRoleService;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    Queue::fake();

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

it('rejects unauthenticated requests to domains show', function (): void {
    TenantContext::flush();

    $response = $this->get(route('settings.domains.show'));

    $response->assertRedirect(route('login'));
});

it('renders the domains settings page for authenticated owner', function (): void {
    $response = $this->actingAs($this->user)->get(route('settings.domains.show'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/domains')
            ->has('organization')
            ->has('domains')
            ->has('baseDomain')
        );
});

it('rejects domain store with invalid domain format', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.domains.store'), [
        'domain' => 'not a valid domain',
    ]);

    $response->assertSessionHasErrors(['domain']);
});

it('rejects domain store with empty domain', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.domains.store'), [
        'domain' => '',
    ]);

    $response->assertSessionHasErrors(['domain']);
});

it('stores a valid custom domain', function (): void {
    $response = $this->actingAs($this->user)->post(route('settings.domains.store'), [
        'domain' => 'custom.example.com',
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    expect(OrganizationDomain::query()->where('domain', 'custom.example.com')->exists())->toBeTrue();
});

it('rejects duplicate domain', function (): void {
    OrganizationDomain::query()->create([
        'organization_id' => $this->organization->id,
        'domain' => 'existing.example.com',
        'type' => 'custom',
        'status' => 'pending_dns',
        'is_verified' => false,
        'is_primary' => false,
    ]);

    $response = $this->actingAs($this->user)->post(route('settings.domains.store'), [
        'domain' => 'existing.example.com',
    ]);

    $response->assertSessionHasErrors(['domain']);
});

it('rejects domain store for non-member user', function (): void {
    $otherUser = createTestUser(['email' => 'other@example.com']);

    $response = $this->actingAs($otherUser)->post(route('settings.domains.store'), [
        'domain' => 'stolen.example.com',
    ]);

    $response->assertForbidden();
});

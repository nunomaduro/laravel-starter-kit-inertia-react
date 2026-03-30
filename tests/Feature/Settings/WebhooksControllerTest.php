<?php

declare(strict_types=1);

use App\Actions\CreateOrganizationAction;
use App\Models\Organization;
use App\Models\WebhookEndpoint;
use App\Services\Organization\OrganizationRoleService;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Http;
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
    $this->actingAs($this->user);
});

afterEach(function (): void {
    TenantContext::flush();
    setPermissionsTeamId(0);
});

it('renders the webhooks index page with endpoints', function (): void {
    WebhookEndpoint::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('settings.webhooks.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/webhooks/index')
            ->has('endpoints', 1)
        );
});

it('renders the webhooks create page', function (): void {
    $response = $this->get(route('settings.webhooks.create'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/webhooks/create')
            ->has('eventGroups')
        );
});

it('stores a webhook endpoint with valid data', function (): void {
    $response = $this->post(route('settings.webhooks.store'), [
        'url' => 'https://example.com/webhook',
        'events' => ['user.created'],
        'description' => 'Test webhook',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('settings.webhooks.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('webhook_endpoints', [
        'organization_id' => $this->organization->id,
        'url' => 'https://example.com/webhook',
        'description' => 'Test webhook',
    ]);
});

it('validates required fields on store', function (): void {
    $response = $this->post(route('settings.webhooks.store'), []);

    $response->assertSessionHasErrors(['url', 'events']);
});

it('renders the webhooks edit page', function (): void {
    $endpoint = WebhookEndpoint::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('settings.webhooks.edit', $endpoint));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/webhooks/edit')
            ->has('endpoint')
            ->has('eventGroups')
        );
});

it('updates a webhook endpoint with valid data', function (): void {
    $endpoint = WebhookEndpoint::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->user->id,
        'url' => 'https://old.example.com/webhook',
        'events' => ['user.created'],
    ]);

    $response = $this->put(route('settings.webhooks.update', $endpoint), [
        'url' => 'https://new.example.com/webhook',
        'events' => ['user.updated'],
        'is_active' => true,
    ]);

    $response->assertRedirect(route('settings.webhooks.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('webhook_endpoints', [
        'id' => $endpoint->id,
        'url' => 'https://new.example.com/webhook',
    ]);
});

it('soft deletes a webhook endpoint', function (): void {
    $endpoint = WebhookEndpoint::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('settings.webhooks.destroy', $endpoint));

    $response->assertRedirect(route('settings.webhooks.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('webhook_endpoints', ['id' => $endpoint->id]);
});

it('returns json status for test ping', function (): void {
    Http::fake(['*' => Http::response(['ok' => true], 200)]);

    $endpoint = WebhookEndpoint::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->user->id,
        'url' => 'https://example.com/webhook',
    ]);

    $response = $this->postJson(route('settings.webhooks.test', $endpoint));

    $response->assertOk()
        ->assertJsonStructure(['status', 'time_ms']);
});

it('cannot access an endpoint from another organization (edit returns 404 due to global scope)', function (): void {
    $otherOrg = Organization::factory()->create();
    $endpoint = WebhookEndpoint::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'url' => 'https://other.example.com/webhook',
        'events' => ['user.created'],
        'secret' => Illuminate\Support\Str::random(32),
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    // BelongsToOrganization global scope prevents route model binding from
    // resolving cross-org endpoints, so the response is 404 rather than 403.
    $response = $this->get(route('settings.webhooks.edit', $endpoint));

    $response->assertNotFound();
});

it('cannot update an endpoint from another organization (returns 404 due to global scope)', function (): void {
    $otherOrg = Organization::factory()->create();
    $endpoint = WebhookEndpoint::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'url' => 'https://other.example.com/webhook',
        'events' => ['user.created'],
        'secret' => Illuminate\Support\Str::random(32),
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response = $this->put(route('settings.webhooks.update', $endpoint), [
        'url' => 'https://stolen.example.com/webhook',
        'events' => ['user.created'],
        'is_active' => true,
    ]);

    $response->assertNotFound();

    // Verify the original URL was not changed.
    $this->assertDatabaseHas('webhook_endpoints', [
        'id' => $endpoint->id,
        'url' => 'https://other.example.com/webhook',
    ]);
});

it('cannot delete an endpoint from another organization (returns 404 due to global scope)', function (): void {
    $otherOrg = Organization::factory()->create();
    $endpoint = WebhookEndpoint::withoutGlobalScopes()->create([
        'organization_id' => $otherOrg->id,
        'url' => 'https://other.example.com/webhook',
        'events' => ['user.created'],
        'secret' => Illuminate\Support\Str::random(32),
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('settings.webhooks.destroy', $endpoint));

    $response->assertNotFound();

    // Verify the endpoint was not deleted.
    $this->assertDatabaseHas('webhook_endpoints', ['id' => $endpoint->id]);
});

it('redirects unauthenticated users to login', function (): void {
    TenantContext::flush();
    auth()->logout();

    $response = $this->get(route('settings.webhooks.index'));

    $response->assertRedirect(route('login'));
});

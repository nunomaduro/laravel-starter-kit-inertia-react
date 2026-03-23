<?php

declare(strict_types=1);

use App\Features\ApiAccessFeature;
use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Laravel\Pennant\Feature;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('unauthenticated request to api v1 users returns 401', function (): void {
    $response = getJson('/api/v1/users');

    $response->assertUnauthorized();
});

test('authenticated user with api access feature inactive receives 404 on api users', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $user->givePermissionTo('view users');
    Feature::for($user)->deactivate(ApiAccessFeature::class);

    $response = actingAs($user, 'sanctum')->getJson('/api/v1/users');

    $response->assertNotFound();
});

test('authenticated user with view users can list users', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $user->givePermissionTo('view users');

    $response = actingAs($user, 'sanctum')->getJson('/api/v1/users');

    $response->assertOk();
    $response->assertJsonStructure(['data', 'links', 'meta']);
});

test('batch creates updates and deletes users and returns counts', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('create users', 'edit users', 'delete users');

    $target = User::factory()->withoutTwoFactor()->create(['name' => 'To Update']);
    $toDelete = User::factory()->withoutTwoFactor()->create();

    $response = actingAs($admin, 'sanctum')->postJson('/api/v1/users/batch', [
        'create' => [
            ['name' => 'Batch Created', 'email' => 'batch-created-'.uniqid('', true).'@example.com', 'password' => 'Password1!'],
        ],
        'update' => [
            ['id' => $target->id, 'name' => 'Updated Name'],
        ],
        'delete' => [$toDelete->id],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.created.0', fn ($id): bool => is_int($id));
    $response->assertJsonPath('data.updated.0', $target->id);
    $response->assertJsonPath('data.deleted.0', $toDelete->id);

    expect(User::query()->find($toDelete->id))->toBeNull();
    expect($target->fresh()?->name)->toBe('Updated Name');
});

test('search returns paginated users with filters', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $user->givePermissionTo('view users');

    User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@example.com']);

    $response = actingAs($user, 'sanctum')->postJson('/api/v1/users/search', [
        'filters' => ['name' => 'Alice'],
        'sort' => '-created_at',
        'per_page' => 10,
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['data', 'links', 'meta']);

    $data = $response->json('data');
    expect($data)->toBeArray();
    expect(collect($data)->pluck('name')->toArray())->toContain('Alice Smith');
});

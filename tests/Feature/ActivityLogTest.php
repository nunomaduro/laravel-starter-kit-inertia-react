<?php

declare(strict_types=1);

use App\Actions\CreateUser;
use App\Enums\ActivityType;
use App\Models\EmbeddingDemo;
use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('user model update creates activity log and does not store password in properties', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'name' => 'Original',
        'email' => 'original@example.com',
    ]);
    $user->assignRole('user');

    $user->update(['name' => 'Updated Name']);

    $activity = Activity::query()
        ->where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->where('description', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->toArray())->not->toHaveKey('password')
        ->and($activity->properties->get('attributes'))->toHaveKey('name')
        ->and($activity->properties->get('attributes')['name'])->toBe('Updated Name');
});

test('embedding demo update creates activity log and does not store embedding in properties', function (): void {
    if (! Illuminate\Support\Facades\Schema::hasTable('embedding_demos')) {
        test()->markTestSkipped('embedding_demos table only exists when using PostgreSQL');
    }

    $demo = EmbeddingDemo::query()->create(['content' => 'Initial']);
    $demo->update(['content' => 'Updated content']);

    $activity = Activity::query()
        ->where('subject_type', EmbeddingDemo::class)
        ->where('subject_id', $demo->id)
        ->where('description', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->toArray())->not->toHaveKey('embedding')
        ->and($activity->properties->get('attributes'))->toHaveKey('content')
        ->and($activity->properties->get('attributes')['content'])->toBe('Updated content');
});

test('two factor enable logs activity', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $user->assignRole('user');

    $enable = resolve(EnableTwoFactorAuthentication::class);
    $enable($user, true);

    assertActivityLogged(ActivityType::TwoFactorEnabled->value, User::class, (int) $user->getKey());
});

test('CreateUser logs roles_assigned when default role is assigned', function (): void {
    $createUser = resolve(CreateUser::class);
    $user = $createUser->handle(
        ['name' => 'New User', 'email' => 'newuser@example.com'],
        'password'
    );

    assertActivityLogged(ActivityType::RolesAssigned->value, User::class, (int) $user->getKey());

    $activity = Activity::query()
        ->where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->where('description', ActivityType::RolesAssigned->value)
        ->latest()
        ->first();
    expect($activity->properties->get('attributes'))->toContain('user');
});

test('role creation logs role_created activity', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $role = Role::query()->create(['name' => 'test-role-activity', 'guard_name' => 'web']);

    assertActivityLogged(ActivityType::RoleCreated->value, Role::class, (int) $role->getKey());
    $activity = Activity::query()
        ->where('subject_type', Role::class)
        ->where('subject_id', $role->id)
        ->where('description', ActivityType::RoleCreated->value)
        ->latest()
        ->first();
    expect($activity->properties->get('name'))->toBe('test-role-activity');

    $role->delete();
});

test('make model full injects LogsActivity and getActivitylogOptions into generated model', function (): void {
    Artisan::call('make:model:full', [
        'name' => 'ActivityLogInjectedModel',
        '--no-interaction' => true,
    ]);

    $path = app_path('Models/ActivityLogInjectedModel.php');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)
        ->toContain('LogsActivity')
        ->toContain('getActivitylogOptions')
        ->toContain('LogOptions::defaults()')
        ->toContain('logExcept');

    unlink($path);
    $migration = glob(database_path('migrations/*_create_activity_log_injected_models_table.php'));
    if ($migration !== []) {
        unlink($migration[0]);
    }

    $specPath = database_path('seeders/specs/ActivityLogInjectedModel.json');
    if (file_exists($specPath)) {
        unlink($specPath);
    }
});

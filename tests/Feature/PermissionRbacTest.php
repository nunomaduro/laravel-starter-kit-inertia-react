<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('check-routes command exits 0 when all application routes have names', function (): void {
    $exit = Artisan::call('permission:check-routes');

    expect($exit)->toBe(0);
});

it('sync-routes command dry run lists route-based permissions', function (): void {
    $exit = Artisan::call('permission:sync-routes', ['--dry-run' => true, '--silent' => false]);

    expect($exit)->toBe(0);
});

it('sync-routes command runs successfully', function (): void {
    $exit = Artisan::call('permission:sync-routes', ['--silent' => true]);

    expect($exit)->toBe(0);
    expect(Permission::query()->where('name', 'bypass-permissions')->exists())->toBeTrue();
});

it('user with bypass-permissions passes gate check for arbitrary ability', function (): void {
    $user = User::withoutEvents(fn () => User::factory()->withoutTwoFactor()->create());
    $user->givePermissionTo('bypass-permissions');

    expect(Gate::forUser($user)->allows('some.arbitrary.permission'))->toBeTrue();
});

it('user without bypass-permissions does not pass arbitrary ability', function (): void {
    $user = User::withoutEvents(fn () => User::factory()->withoutTwoFactor()->create());
    assignRoleForTestUser($user, 'user');

    expect(Gate::forUser($user)->allows('some.arbitrary.permission'))->toBeFalse();
});

it('super-admin role has bypass-permissions', function (): void {
    $superAdmin = Role::query()->where('name', 'super-admin')->first();

    expect($superAdmin)->not->toBeNull();
    expect($superAdmin->hasPermissionTo('bypass-permissions'))->toBeTrue();
});

it('permission:health exits 0 when super-admin role exists', function (): void {
    $exit = Artisan::call('permission:health');

    expect($exit)->toBe(0);
});

it('last super-admin cannot be deleted', function (): void {
    $superAdminUser = User::withoutEvents(fn () => User::factory()->withoutTwoFactor()->create());
    assignRoleForTestUser($superAdminUser, 'super-admin');

    $admin = User::withoutEvents(fn () => User::factory()->withoutTwoFactor()->create());
    assignRoleForTestUser($admin, 'admin');

    expect(Gate::forUser($admin)->denies('delete', $superAdminUser))->toBeTrue();
});

it('create user assigns default role when role exists', function (): void {
    $action = resolve(App\Actions\CreateUser::class);
    $email = 'defaultrole-'.uniqid('', true).'@example.com';

    $user = $action->handle([
        'name' => 'New User',
        'email' => $email,
    ], 'password');

    expect($user->hasRole('user'))->toBeTrue();
});

<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\AssignRoleViaDb;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

it('assigns global roles via AssignRoleViaDb using role id only', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::withoutEvents(function (): User {
        return User::query()->create([
            'name' => 'Assign Via Db',
            'email' => 'assign-via-db@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);
    });

    AssignRoleViaDb::assignGlobal($user, ['super-admin']);

    expect($user->fresh()->hasRole('super-admin'))->toBeTrue();
});

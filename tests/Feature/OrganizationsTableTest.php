<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\SetupWizardSettings;
use App\Settings\TenancySettings;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

test('unauthenticated user cannot access organizations list table page', function (): void {
    $this->get(route('organizations.list'))
        ->assertRedirect();
});

test('authenticated user can access organizations list and receives tableData', function (): void {
    // Route is behind tenancy.enabled — redirect to dashboard when multi-org mode is off
    $tenancy = resolve(TenancySettings::class);
    $tenancy->enabled = true;
    $tenancy->save();

    // Super-admin is redirected to setup wizard until completed (EnsureSetupComplete)
    $setup = resolve(SetupWizardSettings::class);
    $setup->setup_completed = true;
    $setup->save();

    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(static fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@organizations-table-test.example',
        'password' => Hash::make('password'),
    ]));
    assignRoleForTestUser($user, 'super-admin');

    $response = $this->actingAs($user)
        ->get(route('organizations.list'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('organizations/table')
        ->has('tableData')
        ->has('tableData.data')
        ->has('tableData.columns')
        ->has('tableData.meta')
        ->has('searchableColumns')
        ->where('tableData.meta.total', fn ($total): bool => $total >= 0)
    );
});

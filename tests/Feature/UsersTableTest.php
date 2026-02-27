<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

test('unauthenticated user cannot access users table page', function (): void {
    $this->get(route('users.table'))
        ->assertRedirect();
});

test('authenticated user with permission can access users table and receives tableData', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@users-table-test.example',
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)
        ->get(route('users.table'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('users/table')
        ->has('tableData')
        ->has('tableData.data')
        ->has('tableData.columns')
        ->has('tableData.meta')
        ->has('searchableColumns')
        ->where('tableData.meta.total', fn ($total): bool => $total >= 0)
    );
});

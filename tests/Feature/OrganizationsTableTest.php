<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

test('unauthenticated user cannot access organizations list table page', function (): void {
    $this->get(route('organizations.list'))
        ->assertRedirect();
});

test('authenticated user can access organizations list and receives tableData', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@organizations-table-test.example',
        'password' => Hash::make('password'),
    ]));
    $user->assignRole('super-admin');

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

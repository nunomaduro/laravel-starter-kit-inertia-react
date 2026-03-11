<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

test('unauthenticated user cannot access categories table page', function (): void {
    $this->get(route('categories.table'))
        ->assertRedirect();
});

test('authenticated user can access categories table and receives tableData', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@categories-table-test.example',
        'password' => Hash::make('password'),
    ]));
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)
        ->get(route('categories.table'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('categories/table')
        ->has('tableData')
        ->has('tableData.data')
        ->has('tableData.columns')
        ->has('tableData.meta')
        ->has('searchableColumns')
        ->where('tableData.meta.total', fn ($total): bool => $total >= 0)
    );
});

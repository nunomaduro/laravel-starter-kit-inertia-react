<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

test('unauthenticated user cannot access posts table page', function (): void {
    $this->get(route('posts.table'))
        ->assertRedirect();
});

test('authenticated user can access posts table and receives tableData', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@posts-table-test.example',
        'password' => Hash::make('password'),
    ]));
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)
        ->get(route('posts.table'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('posts/table')
        ->has('tableData')
        ->has('tableData.data')
        ->has('tableData.columns')
        ->has('tableData.meta')
        ->has('searchableColumns')
        ->where('tableData.meta.total', fn ($total): bool => $total >= 0)
    );
});

test('authenticated user can request posts export', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@posts-export.example',
        'password' => Hash::make('password'),
    ]));
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)
        ->get(route('data-table.export', ['table' => 'posts']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

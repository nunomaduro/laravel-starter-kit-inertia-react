<?php

declare(strict_types=1);

use App\Models\User;

test('unauthenticated user cannot access users table page', function (): void {
    $this->get(route('users.table'))
        ->assertRedirect();
});

test('authenticated user can access users table and receives tableData', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user)
        ->get(route('users.table'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('users/table')
        ->has('tableData')
        ->has('tableData.data')
        ->has('tableData.columns')
        ->has('tableData.meta')
        ->where('tableData.meta.total', fn ($total) => $total >= 0)
    );
});

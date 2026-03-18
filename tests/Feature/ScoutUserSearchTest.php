<?php

declare(strict_types=1);

use App\Models\User;

test('user model is searchable via scout', function (): void {
    $user = User::withoutEvents(fn (): User => User::factory()->create(['name' => 'Scout Searchable User']));

    $results = User::search('Scout Searchable')->get();

    expect($results->pluck('id')->toArray())->toContain($user->id);
});

test('user toSearchableArray returns required types for Typesense', function (): void {
    $user = User::withoutEvents(fn (): User => User::factory()->create(['name' => 'Test', 'email' => 'scout-typesense@example.com']));

    $array = $user->toSearchableArray();

    expect($array)
        ->toHaveKeys(['id', 'name', 'email', 'created_at'])
        ->and($array['id'])->toBeString()
        ->and($array['created_at'])->toBeInt()
        ->and($array['name'])->toBe('Test')
        ->and($array['email'])->toBe('scout-typesense@example.com');
});

<?php

declare(strict_types=1);

use App\Actions\DuplicateUser;
use App\Models\Organization;
use App\Models\User;

it('creates a copy of the user with modified name and email', function (): void {
    $user = User::factory()->withoutTwoFactor()->create(['name' => 'Jane Doe']);

    $action = resolve(DuplicateUser::class);
    $copy = $action->handle($user);

    expect($copy)->toBeInstanceOf(User::class)
        ->and($copy->id)->not->toBe($user->id)
        ->and($copy->name)->toBe('Jane Doe (copy)')
        ->and($copy->email)->not->toBe($user->email);
});

it('copies the user organization memberships', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($user->id, [
        'is_default' => true,
        'joined_at' => now(),
    ]);

    $action = resolve(DuplicateUser::class);
    $copy = $action->handle($user);

    expect($copy->organizations()->pluck('organizations.id')->all())
        ->toContain($org->id);
});

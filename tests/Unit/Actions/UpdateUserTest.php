<?php

declare(strict_types=1);

use App\Actions\UpdateUser;
use App\Models\User;

it('may update a user', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@email.com',
    ]);

    $action = app(UpdateUser::class);

    $action->handle($user, [
        'name' => 'New Name',
    ]);

    expect($user->refresh()->name)->toBe('New Name')
        ->and($user->email)->toBe('old@email.com');
});

it('resets email verification when email changes', function (): void {
    $user = User::factory()->create([
        'email' => 'old@email.com',
        'email_verified_at' => now(),
    ]);

    expect($user->email_verified_at)->not->toBeNull();

    $action = app(UpdateUser::class);

    $action->handle($user, [
        'email' => 'new@email.com',
    ]);

    expect($user->refresh()->email)->toBe('new@email.com')
        ->and($user->email_verified_at)->toBeNull();
});

it('keeps email verification when email stays the same', function (): void {
    $verifiedAt = now();

    $user = User::factory()->create([
        'email' => 'same@email.com',
        'email_verified_at' => $verifiedAt,
    ]);

    $action = app(UpdateUser::class);

    $action->handle($user, [
        'email' => 'same@email.com',
        'name' => 'Updated Name',
    ]);

    expect($user->refresh()->email_verified_at)->not->toBeNull()
        ->and($user->name)->toBe('Updated Name');
});

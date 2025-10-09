<?php

declare(strict_types=1);

use App\Actions\CreateUserPassword;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('may create a new user password', function (): void {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Password::createToken($user);

    $action = app(CreateUserPassword::class);

    $status = $action->handle([
        'email' => $user->email,
        'token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ], 'new-password');

    expect($status)->toBe(Password::PASSWORD_RESET)
        ->and(Hash::check('new-password', $user->refresh()->password))->toBeTrue();

    Event::assertDispatched(PasswordReset::class);
});

it('returns invalid token status for incorrect token', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $action = app(CreateUserPassword::class);

    $status = $action->handle([
        'email' => $user->email,
        'token' => 'invalid-token',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ], 'new-password');

    expect($status)->toBe(Password::INVALID_TOKEN);
});

it('returns invalid user status for non-existent email', function (): void {
    $action = app(CreateUserPassword::class);

    $status = $action->handle([
        'email' => 'nonexistent@example.com',
        'token' => 'some-token',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ], 'new-password');

    expect($status)->toBe(Password::INVALID_USER);
});

it('updates remember token when resetting password', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'remember_token' => 'old-token',
    ]);

    $token = Password::createToken($user);

    $action = app(CreateUserPassword::class);

    $action->handle([
        'email' => $user->email,
        'token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ], 'new-password');

    expect($user->refresh()->remember_token)->not->toBe('old-token')
        ->and($user->remember_token)->not->toBeNull();
});

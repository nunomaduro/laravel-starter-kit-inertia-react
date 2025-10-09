<?php

declare(strict_types=1);

use App\Actions\CreateUserEmailResetNotification;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('may send password reset notification', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $action = app(CreateUserEmailResetNotification::class);

    $status = $action->handle(['email' => $user->email]);

    expect($status)->toBe(Password::RESET_LINK_SENT);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('returns throttled status when too many attempts', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $action = app(CreateUserEmailResetNotification::class);

    // Send multiple reset requests to trigger throttling
    $action->handle(['email' => $user->email]);
    $status = $action->handle(['email' => $user->email]);

    expect($status)->toBe(Password::RESET_THROTTLED);
});

it('returns invalid user status for non-existent email', function (): void {
    $action = app(CreateUserEmailResetNotification::class);

    $status = $action->handle(['email' => 'nonexistent@example.com']);

    expect($status)->toBe(Password::INVALID_USER);
});

<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final readonly class UpdateUser
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes): void
    {
        $emailChanged = array_key_exists('email', $attributes) && $user->email !== $attributes['email'];

        $user->update([...$attributes, ...($emailChanged ? ['email_verified_at' => null] : [])]);

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}

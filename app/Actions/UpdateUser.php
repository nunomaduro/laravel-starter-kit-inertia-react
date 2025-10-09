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
        $email = $attributes['email'] ?? null;

        $user->update([
            ...$attributes,
            ...$user->email === $email ? [] : ['email_verified_at' => null],
        ]);
    }
}

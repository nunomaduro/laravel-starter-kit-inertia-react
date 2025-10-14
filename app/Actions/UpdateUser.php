<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\UserData;
use App\Models\User;

final readonly class UpdateUser
{
    public function handle(User $user, UserData $data): void
    {
        $updates = $data->toArray();

        // Only reset email verification if email is being updated
        if ($data->email !== null && $user->email !== $data->email) {
            $updates['email_verified_at'] = null;
        }

        $user->update($updates);
    }
}

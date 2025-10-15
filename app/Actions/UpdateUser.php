<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UserData;
use App\Models\User;

final readonly class UpdateUser
{
    public function handle(User $user, UserData $data): void
    {
        $updates = array_filter($data->toArray(), fn ($value) => $value !== null);

        if (! empty($updates['email']) && $updates['email'] !== $user->email) {
            $updates['email_verified_at'] = null;
        }

        if (! empty($updates)) {
            $user->update($updates);
        }
    }
}

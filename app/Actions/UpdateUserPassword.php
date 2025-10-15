<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AuthData;
use App\Models\User;

final readonly class UpdateUserPassword
{
    public function handle(User $user, AuthData $data): void
    {
        $user->update([
            'password' => $data->password,
        ]);
    }
}

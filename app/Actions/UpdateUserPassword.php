<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use SensitiveParameter;

final readonly class UpdateUserPassword
{
    public function handle(User $user, #[SensitiveParameter] string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }
}

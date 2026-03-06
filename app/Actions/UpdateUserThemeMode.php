<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final readonly class UpdateUserThemeMode
{
    public function handle(User $user, string $mode): void
    {
        $user->theme_mode = $mode;
        $user->save();
    }
}

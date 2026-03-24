<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateUserThemeMode;
use App\Http\Requests\UpdateUserPreferencesRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

final readonly class UserPreferencesController
{
    public function update(UpdateUserPreferencesRequest $request, #[CurrentUser] User $user, UpdateUserThemeMode $action): RedirectResponse
    {
        $action->handle($user, $request->validated('theme_mode'));

        return back();
    }
}

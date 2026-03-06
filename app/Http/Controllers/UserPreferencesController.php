<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateUserThemeMode;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final readonly class UserPreferencesController
{
    public function update(Request $request, #[CurrentUser] User $user, UpdateUserThemeMode $action): RedirectResponse
    {
        $validated = $request->validate([
            'theme_mode' => ['required', 'string', 'in:dark,light,system'],
        ]);

        $action->handle($user, $validated['theme_mode']);

        return back();
    }
}

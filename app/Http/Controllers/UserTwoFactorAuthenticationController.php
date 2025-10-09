<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ShowUserTwoFactorAuthenticationRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

final readonly class UserTwoFactorAuthenticationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [new Middleware('password.confirm', only: ['show'])]
            : [];
    }

    public function show(ShowUserTwoFactorAuthenticationRequest $request, #[CurrentUser] User $user): Response
    {
        $request->ensureStateIsValid();

        return Inertia::render('user-two-factor-authentication/show', [
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
        ]);
    }
}

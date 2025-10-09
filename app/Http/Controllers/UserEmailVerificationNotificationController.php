<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUserEmailVerificationNotification;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UserEmailVerificationNotificationController
{
    public function create(Request $request, #[CurrentUser] User $user): Response|RedirectResponse
    {
        return $user->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard', absolute: false))
            : Inertia::render('user-email-verification-notification/create', ['status' => $request->session()->get('status')]);
    }

    public function store(#[CurrentUser] User $user, CreateUserEmailVerificationNotification $action): RedirectResponse
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $action->handle($user);

        return back()->with('status', 'verification-link-sent');
    }
}

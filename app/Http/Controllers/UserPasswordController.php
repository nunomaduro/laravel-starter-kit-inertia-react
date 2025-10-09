<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResetPassword;
use App\Actions\UpdateUserPassword;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class UserPasswordController
{
    public function create(Request $request): Response
    {
        return Inertia::render('auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    public function store(ResetPasswordRequest $request, ResetPassword $action): RedirectResponse
    {
        /** @var array<string, mixed> $credentials */
        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        $status = $action->handle(
            $credentials,
            $request->string('password')->value()
        );

        throw_if($status !== Password::PASSWORD_RESET, ValidationException::withMessages([
            'email' => [__(is_string($status) ? $status : '')],
        ]));

        return to_route('login')->with('status', __('passwords.reset'));
    }

    public function edit(): Response
    {
        return Inertia::render('settings/password');
    }

    public function update(UpdateUserPasswordRequest $request, #[CurrentUser] User $user, UpdateUserPassword $action): RedirectResponse
    {
        $action->handle($user, $request->string('password')->value());

        return back();
    }
}

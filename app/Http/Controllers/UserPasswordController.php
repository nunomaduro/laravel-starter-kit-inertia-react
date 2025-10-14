<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateUserPassword;
use App\Actions\UpdateUserPassword;
use App\DTOs\AuthData;
use App\Http\Requests\CreateUserPasswordRequest;
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
        return Inertia::render('user-password/create', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    public function store(CreateUserPasswordRequest $request, CreateUserPassword $action): RedirectResponse
    {
        $credentials = AuthData::from($request->validated());

        $status = $action->handle($credentials);

        throw_if($status !== Password::PASSWORD_RESET, ValidationException::withMessages([
            'email' => [__(is_string($status) ? $status : '')],
        ]));

        return to_route('login')->with('status', __('passwords.reset'));
    }

    public function edit(): Response
    {
        return Inertia::render('user-password/edit');
    }

    public function update(UpdateUserPasswordRequest $request, #[CurrentUser] User $user, UpdateUserPassword $action): RedirectResponse
    {
        $data = AuthData::from($request->validated());

        $action->handle($user, $data);

        return back();
    }
}

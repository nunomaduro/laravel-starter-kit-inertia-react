<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\AuthData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final readonly class CreateUserPassword
{
    public function handle(AuthData $credentials): mixed
    {
        return Password::reset(
            $credentials->toArray(),
            function (User $user) use ($credentials): void {
                $user->update([
                    'password' => $credentials->password,
                    'remember_token' => Str::random(60),
                ]);

                event(new PasswordReset($user));
            }
        );
    }
}

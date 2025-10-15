<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AuthData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final readonly class CreateUserPassword
{
    public function handle(AuthData $credentials): mixed
    {
        $data = array_filter($credentials->toArray(), fn ($value) => $value !== null);

        return Password::reset(
            $data,
            function (User $user) use ($credentials): void {
                $user->forceFill([
                    'password' => $credentials->password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }
}

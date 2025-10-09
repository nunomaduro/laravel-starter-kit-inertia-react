<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use SensitiveParameter;

final readonly class CreateUserPassword
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function handle(array $credentials, #[SensitiveParameter] string $password): mixed
    {
        return Password::reset(
            $credentials,
            function (User $user) use ($password): void {
                $user->update([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ]);

                event(new PasswordReset($user));
            }
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\UserData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

final readonly class CreateUser
{
    public function handle(UserData $data): User
    {
        // Registration always requires password
        assert($data->password !== null, 'Password is required for user registration');

        $user = User::query()->create([
            ...$data->toArray(),
            'password' => $data->password,
        ]);

        event(new Registered($user));

        return $user;
    }
}

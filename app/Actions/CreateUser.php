<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UserData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

final readonly class CreateUser
{
    public function handle(UserData $data): User
    {
        // Registration always requires password
        assert($data->password !== null, 'Password is required for user registration');

        $attributes = array_filter($data->toArray(), fn ($value) => $value !== null);

        $user = User::query()->create([
            ...$attributes,
            'password' => $data->password,
        ]);

        event(new Registered($user));

        return $user;
    }
}

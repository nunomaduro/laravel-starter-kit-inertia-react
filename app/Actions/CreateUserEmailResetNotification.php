<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Password;

final readonly class CreateUserEmailResetNotification
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function handle(array $credentials): string
    {
        return Password::sendResetLink($credentials);
    }
}

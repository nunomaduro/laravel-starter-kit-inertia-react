<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Validation\Rules\Password;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\CurrentPassword;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class AuthData extends Data
{
    public function __construct(
        #[Required]
        #[Confirmed]
        public string $password,
        #[Email]
        public ?string $email = null,
        public ?string $token = null,
        #[CurrentPassword]
        public ?string $currentPassword = null,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'password' => [
                Password::defaults(),
            ],
        ];
    }
}

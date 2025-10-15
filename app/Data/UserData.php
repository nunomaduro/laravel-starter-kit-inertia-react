<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\User;
use App\Rules\ValidEmail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use SensitiveParameter;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

final class UserData extends Data
{
    public function __construct(
        public ?int $id = null,
        #[Required]
        #[StringType]
        #[Max(255)]
        public ?string $name = null,
        #[Required]
        #[StringType]
        #[Email]
        #[Max(255)]
        public ?string $email = null,
        #[SensitiveParameter]
        #[Confirmed]
        public ?string $password = null,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(ValidationContext $context): array
    {
        $userId = is_array($context->payload) ? ($context->payload['id'] ?? null) : null;

        return [
            'email' => [
                'lowercase',
                new ValidEmail,
                Rule::unique(User::class)->ignore($userId),
            ],
            'password' => [
                Password::defaults(),
            ],
        ];
    }

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
        );
    }
}

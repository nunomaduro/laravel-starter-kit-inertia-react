<?php

declare(strict_types=1);

namespace App\DTOs;

use SensitiveParameter;

final readonly class UserData
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        #[SensitiveParameter]
        public ?string $password = null,
    ) {}

    /**
     * @param  array<string, string|null>  $data
     */
    public static function from(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
        ], fn (?string $v): bool => $v !== null);
    }
}

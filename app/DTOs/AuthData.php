<?php

declare(strict_types=1);

namespace App\DTOs;

use SensitiveParameter;

final readonly class AuthData
{
    public function __construct(
        #[SensitiveParameter] public string $password,
        public ?string $email = null,
        public ?string $token = null,
        #[SensitiveParameter] public ?string $currentPassword = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function from(array $data): self
    {
        assert(is_string($data['password']));

        $email = $data['email'] ?? null;
        $token = $data['token'] ?? null;
        $currentPassword = $data['current_password'] ?? null;

        assert($email === null || is_string($email));
        assert($token === null || is_string($token));
        assert($currentPassword === null || is_string($currentPassword));

        return new self(
            password: $data['password'],
            email: $email,
            token: $token,
            currentPassword: $currentPassword,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $data = [
            'password' => $this->password,
        ];

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        if ($this->token !== null) {
            $data['token'] = $this->token;
        }

        return $data;
    }
}

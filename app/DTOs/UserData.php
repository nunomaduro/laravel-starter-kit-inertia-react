<?php

declare(strict_types=1);

namespace App\DTOs;

use SensitiveParameter;

final readonly class UserData
{
    public function __construct(
        public ?string $name,
        public ?string $email,
        #[SensitiveParameter] public ?string $password = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function from(array $data): self
    {
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        assert($name === null || is_string($name));
        assert($email === null || is_string($email));
        assert($password === null || is_string($password));

        return new self(
            name: $name,
            email: $email,
            password: $password,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        return $data;
    }
}

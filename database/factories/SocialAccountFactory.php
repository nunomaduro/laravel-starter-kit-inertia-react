<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialAccount>
 */
final class SocialAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['github', 'google', 'facebook', 'twitter']),
            'provider_id' => (string) fake()->unique()->randomNumber(8),
            'token' => fake()->sha256(),
            'refresh_token' => fake()->sha256(),
            'token_expires_at' => now()->addHour(),
        ];
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes): array => [
            'token_expires_at' => now()->subHour(),
        ]);
    }
}

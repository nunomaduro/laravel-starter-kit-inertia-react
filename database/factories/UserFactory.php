<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'role' => 'guest',
            'phone' => null,
            'avatar' => null,
            'bio' => null,
            'commission_rate' => null,
        ];
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): self
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, fn (): string => RecoveryCode::generate())->all())),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function host(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'host',
            'commission_rate' => '0.1000',
            'phone' => fake()->phoneNumber(),
        ]);
    }

    public function admin(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'admin',
        ]);
    }
}

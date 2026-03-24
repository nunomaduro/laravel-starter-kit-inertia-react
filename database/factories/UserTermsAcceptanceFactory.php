<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TermsVersion;
use App\Models\User;
use App\Models\UserTermsAcceptance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserTermsAcceptance>
 */
final class UserTermsAcceptanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'terms_version_id' => TermsVersion::factory(),
            'accepted_at' => now(),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}

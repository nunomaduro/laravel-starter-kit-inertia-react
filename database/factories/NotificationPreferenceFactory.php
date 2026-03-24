<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\NotificationPreference>
 */
final class NotificationPreferenceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_type' => fake()->word(),
            'via_database' => fake()->boolean(),
            'via_email' => fake()->boolean(),
        ];
    }
}

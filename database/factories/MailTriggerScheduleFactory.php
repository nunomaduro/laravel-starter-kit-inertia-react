<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MailTriggerSchedule;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailTriggerSchedule>
 */
final class MailTriggerScheduleFactory extends Factory
{
    protected $model = MailTriggerSchedule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'event_class' => fake()->randomElement(config('database-mail.events', [
                'App\\Events\\User\\UserCreated',
            ])),
            'template_id' => null,
            'delay_minutes' => fake()->optional()->numberBetween(5, 1440),
            'is_active' => true,
            'feature_flag' => null,
            'created_by' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function withDelay(int $minutes): static
    {
        return $this->state(fn (array $attributes): array => [
            'delay_minutes' => $minutes,
        ]);
    }

    public function withFeatureFlag(string $flag): static
    {
        return $this->state(fn (array $attributes): array => [
            'feature_flag' => $flag,
        ]);
    }
}

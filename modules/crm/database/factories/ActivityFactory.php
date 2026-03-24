<?php

declare(strict_types=1);

namespace Modules\Crm\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Crm\Models\Activity;
use Modules\Crm\Models\Contact;
use Modules\Crm\Models\Deal;

/**
 * @extends Factory<Activity>
 */
final class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'deal_id' => null,
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['call', 'email', 'meeting', 'note', 'task']),
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'completed_at' => null,
        ];
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'completed_at' => now(),
            'scheduled_at' => fake()->dateTimeBetween('-2 weeks', '-1 day'),
        ]);
    }

    public function forDeal(): self
    {
        return $this->state(fn (array $attributes): array => [
            'deal_id' => Deal::factory(),
        ]);
    }

    public function ofType(string $type): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }
}

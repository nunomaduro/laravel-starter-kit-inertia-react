<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookEndpoint>
 */
final class WebhookEndpointFactory extends Factory
{
    protected $model = WebhookEndpoint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allEvents = collect(config('webhooks.events', []))
            ->flatMap(fn (array $group): array => array_keys($group))
            ->all();

        return [
            'organization_id' => Organization::factory(),
            'url' => fake()->url().'/webhooks',
            'events' => fake()->randomElements($allEvents, min(2, count($allEvents))),
            'secret' => Str::random(32),
            'is_active' => true,
            'description' => fake()->optional(0.7)->sentence(3),
            'created_by' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    /**
     * @param  list<string>  $events
     */
    public function forEvents(array $events): static
    {
        return $this->state(fn (): array => ['events' => $events]);
    }
}

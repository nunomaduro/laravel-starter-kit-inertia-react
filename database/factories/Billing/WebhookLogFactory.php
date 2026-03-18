<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Models\Billing\WebhookLog;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookLog>
 */
final class WebhookLogFactory extends Factory
{
    protected $model = WebhookLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'gateway' => fake()->randomElement(['stripe', 'paddle']),
            'event_type' => fake()->randomElement(['invoice.paid', 'customer.subscription.updated', 'checkout.completed']),
            'payload' => ['event_id' => fake()->uuid(), 'created' => now()->timestamp],
            'processed' => fake()->boolean(80),
            'response' => null,
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes): array => ['processed' => true]);
    }

    public function unprocessed(): static
    {
        return $this->state(fn (array $attributes): array => ['processed' => false]);
    }
}

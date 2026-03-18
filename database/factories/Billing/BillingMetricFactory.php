<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Models\Billing\BillingMetric;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingMetric>
 */
final class BillingMetricFactory extends Factory
{
    protected $model = BillingMetric::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mrr = fake()->numberBetween(0, 50000);
        $arr = $mrr * 12;

        return [
            'organization_id' => Organization::factory(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'mrr' => $mrr,
            'arr' => $arr,
            'new_subscriptions' => fake()->numberBetween(0, 5),
            'churned' => fake()->numberBetween(0, 2),
            'credits_purchased' => fake()->numberBetween(0, 1000),
            'credits_used' => fake()->numberBetween(0, 500),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\RefundRequest;
use Modules\Billing\States\RefundRequest\Approved;
use Modules\Billing\States\RefundRequest\Pending;
use Modules\Billing\States\RefundRequest\Processed;

/**
 * @extends Factory<RefundRequest>
 */
final class RefundRequestFactory extends Factory
{
    protected $model = RefundRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->numberBetween(500, 10000),
            'reason' => fake()->optional(0.8)->sentence(),
            'status' => Pending::class,
            'processed_at' => null,
            'processed_by' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Approved::class,
            'processed_at' => now(),
        ]);
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Processed::class,
            'processed_at' => now(),
        ]);
    }
}

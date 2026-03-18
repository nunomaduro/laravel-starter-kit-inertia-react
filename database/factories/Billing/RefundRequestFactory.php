<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Enums\Billing\RefundStatus;
use App\Models\Billing\Invoice;
use App\Models\Billing\RefundRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'status' => RefundStatus::Pending,
            'processed_at' => null,
            'processed_by' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RefundStatus::Approved,
            'processed_at' => now(),
        ]);
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RefundStatus::Processed,
            'processed_at' => now(),
        ]);
    }
}

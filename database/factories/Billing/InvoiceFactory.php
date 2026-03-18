<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Models\Billing\Invoice;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
final class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000, 50000);
        $tax = (int) round($subtotal * 0.1);
        $total = $subtotal + $tax;

        return [
            'billable_type' => Organization::class,
            'billable_id' => Organization::factory(),
            'number' => 'INV-'.now()->format('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => 'draft',
            'currency' => 'usd',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'paid_at' => null,
            'due_date' => now()->addDays(14),
            'line_items' => null,
            'billing_address' => null,
            'payment_gateway_id' => null,
            'gateway_invoice_id' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}

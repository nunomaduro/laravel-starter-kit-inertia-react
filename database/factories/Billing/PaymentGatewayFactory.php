<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Enums\Billing\PaymentGatewayType;
use App\Models\Billing\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends Factory<PaymentGateway>
 */
final class PaymentGatewayFactory extends Factory
{
    protected $model = PaymentGateway::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Stripe', 'Paddle', 'Lemon Squeezy', 'Manual']),
            'type' => fake()->randomElement(PaymentGatewayType::class),
            'settings' => null,
            'is_active' => true,
            'is_default' => false,
            'supported_currencies' => ['usd', 'eur'],
            'supported_payment_methods' => ['card'],
            'sort_order' => 0,
        ];
    }

    public function withEncryptedSettings(): static
    {
        return $this->state(fn (array $attributes): array => [
            'settings' => Crypt::encryptString('{}'),
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => ['is_default' => true]);
    }
}

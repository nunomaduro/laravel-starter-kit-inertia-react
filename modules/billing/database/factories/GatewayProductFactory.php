<?php

declare(strict_types=1);

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\GatewayProduct;
use Modules\Billing\Models\PaymentGateway;

/**
 * @extends Factory<GatewayProduct>
 */
final class GatewayProductFactory extends Factory
{
    protected $model = GatewayProduct::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_gateway_id' => PaymentGateway::factory(),
            'plan_id' => 1,
            'gateway_product_id' => 'prod_'.fake()->unique()->regexify('[A-Za-z0-9]{14}'),
            'gateway_price_id' => fake()->optional(0.7)->passthrough('price_'.fake()->regexify('[A-Za-z0-9]{14}')),
        ];
    }
}

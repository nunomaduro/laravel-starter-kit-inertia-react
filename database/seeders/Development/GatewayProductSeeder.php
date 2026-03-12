<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\GatewayProduct;
use App\Models\Billing\PaymentGateway;
use App\Models\Billing\Plan;
use Illuminate\Database\Seeder;

final class GatewayProductSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['PlanSeeder', 'PaymentGatewaySeeder'];

    public function run(): void
    {
        $plans = Plan::query()->get();
        $gateways = PaymentGateway::query()->get();

        if ($plans->isEmpty() || $gateways->isEmpty()) {
            return;
        }

        $created = 0;
        $target = min(4, $plans->count() * $gateways->count());

        foreach ($plans as $plan) {
            foreach ($gateways as $gateway) {
                if ($created >= $target) {
                    break 2;
                }
                GatewayProduct::query()->firstOrCreate(
                    [
                        'payment_gateway_id' => $gateway->id,
                        'plan_id' => $plan->id,
                    ],
                    [
                        'gateway_product_id' => 'prod_'.fake()->unique()->regexify('[A-Za-z0-9]{14}'),
                        'gateway_price_id' => fake()->optional(0.7)->passthrough('price_'.fake()->regexify('[A-Za-z0-9]{14}')),
                    ]
                );
                $created++;
            }
        }
    }
}

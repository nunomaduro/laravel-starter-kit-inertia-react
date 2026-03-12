<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Enums\Billing\PaymentGatewayType;
use App\Models\Billing\PaymentGateway;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

final class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Stripe',
                'type' => PaymentGatewayType::Stripe,
                'settings' => Crypt::encryptString('{}'),
                'is_active' => true,
                'is_default' => true,
                'supported_currencies' => ['usd', 'eur'],
                'supported_payment_methods' => ['card'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Paddle',
                'type' => PaymentGatewayType::Paddle,
                'settings' => null,
                'is_active' => true,
                'is_default' => false,
                'supported_currencies' => ['usd', 'eur'],
                'supported_payment_methods' => ['card'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Manual',
                'type' => PaymentGatewayType::Manual,
                'settings' => null,
                'is_active' => true,
                'is_default' => false,
                'supported_currencies' => ['usd'],
                'supported_payment_methods' => [],
                'sort_order' => 3,
            ],
        ];

        foreach ($gateways as $attributes) {
            // Avoid SortableTrait creating hook using wrong order column when table is empty/new.
            PaymentGateway::withoutEvents(function () use ($attributes): void {
                PaymentGateway::query()->firstOrCreate(
                    ['name' => $attributes['name']],
                    $attributes
                );
            });
        }
    }
}

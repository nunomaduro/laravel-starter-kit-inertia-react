<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\CreditPack;

final class CreditPackSeeder extends Seeder
{
    public function run(): void
    {
        CreditPack::query()->firstOrCreate(
            ['slug' => 'starter-credits'],
            [
                'name' => 'Starter Pack',
                'description' => '100 credits to get started',
                'credits' => 100,
                'bonus_credits' => 0,
                'price' => 999,
                'currency' => 'usd',
                'validity_days' => 365,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        CreditPack::query()->firstOrCreate(
            ['slug' => 'pro-credits'],
            [
                'name' => 'Pro Pack',
                'description' => '500 credits + 50 bonus',
                'credits' => 500,
                'bonus_credits' => 50,
                'price' => 3999,
                'currency' => 'usd',
                'validity_days' => 365,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\Plan;
use Illuminate\Database\Seeder;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => ['en' => 'Basic'],
                'slug' => 'basic',
                'price' => 0,
                'is_per_seat' => false,
                'price_per_seat' => 0,
                'currency' => 'usd',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
            ],
            [
                'name' => ['en' => 'Pro'],
                'slug' => 'pro',
                'price' => 29,
                'is_per_seat' => false,
                'price_per_seat' => 0,
                'currency' => 'usd',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
            ],
            [
                'name' => ['en' => 'Team'],
                'slug' => 'team',
                'price' => 0,
                'is_per_seat' => true,
                'price_per_seat' => 10,
                'currency' => 'usd',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
            ],
        ];

        foreach ($plans as $attributes) {
            Plan::query()->firstOrCreate(
                ['slug' => $attributes['slug']],
                $attributes
            );
        }
    }
}

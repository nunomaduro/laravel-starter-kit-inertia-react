<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Plan;

/**
 * Seeds the AI-Native App Factory platform subscription tiers.
 *
 * Solo ($29/mo): 1 active project, all modules, BYOK AI
 * Agency ($99/mo): 5 active projects, all modules, 10k AI calls/mo
 * Enterprise ($299/mo): Unlimited projects, white-label, dedicated AI
 */
final class PlatformPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => ['en' => 'Solo'],
                'slug' => 'platform-solo',
                'description' => ['en' => '1 active project, all modules, bring your own AI keys'],
                'price' => 29,
                'is_per_seat' => false,
                'price_per_seat' => 0,
                'currency' => 'usd',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
                'trial_period' => 14,
                'trial_interval' => 'day',
                'sort_order' => 10,
            ],
            [
                'name' => ['en' => 'Agency'],
                'slug' => 'platform-agency',
                'description' => ['en' => '5 active projects, all modules, 10k AI calls/month'],
                'price' => 99,
                'is_per_seat' => false,
                'price_per_seat' => 0,
                'currency' => 'usd',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
                'trial_period' => 14,
                'trial_interval' => 'day',
                'sort_order' => 20,
            ],
            [
                'name' => ['en' => 'Enterprise'],
                'slug' => 'platform-enterprise',
                'description' => ['en' => 'Unlimited projects, white-label, dedicated AI, priority support'],
                'price' => 299,
                'is_per_seat' => false,
                'price_per_seat' => 0,
                'currency' => 'usd',
                'invoice_period' => 1,
                'invoice_interval' => 'month',
                'sort_order' => 30,
            ],
        ];

        foreach ($plans as $attributes) {
            Plan::query()->firstOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use Illuminate\Database\Seeder;
use Modules\Billing\Models\Plan;

final class SubscriptionSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder', 'PlanSeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(3)->get();
        $plans = Plan::query()->get();

        if ($organizations->isEmpty() || $plans->isEmpty()) {
            return;
        }

        foreach ($organizations as $index => $org) {
            $plan = $plans->get($index % $plans->count());
            $name = is_array($plan->name) ? ($plan->name['en'] ?? 'Subscription') : 'Subscription';

            $org->planSubscriptions()->create([
                'name' => ['en' => $name],
                'slug' => 'dev-sub-'.$org->id.'-'.uniqid(),
                'plan_id' => $plan->id,
                'quantity' => 1,
                'starts_at' => now()->subDays(fake()->numberBetween(0, 30)),
                'ends_at' => now()->addMonth(),
            ]);
        }
    }
}

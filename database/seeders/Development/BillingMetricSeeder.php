<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\BillingMetric;
use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;

final class BillingMetricSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(3)->get();

        if ($organizations->isEmpty()) {
            return;
        }

        $daysPerOrg = fake()->numberBetween(10, 15);

        foreach ($organizations as $org) {
            TenantContext::set($org);

            for ($i = 0; $i < $daysPerOrg; $i++) {
                BillingMetric::factory()->create([
                    'date' => now()->subDays($daysPerOrg - 1 - $i),
                ]);
            }

            TenantContext::forget();
        }
    }
}

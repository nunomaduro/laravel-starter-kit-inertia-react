<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Enums\Billing\CreditTransactionType;
use App\Models\Billing\Credit;
use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;

final class CreditSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(5)->get();

        if ($organizations->isEmpty()) {
            return;
        }

        $totalCredits = fake()->numberBetween(10, 15);

        foreach ($organizations as $org) {
            TenantContext::set($org);

            $runningBalance = 0;
            $perOrg = (int) ceil($totalCredits / $organizations->count());

            for ($i = 0; $i < $perOrg; $i++) {
                $type = fake()->randomElement([
                    CreditTransactionType::Purchase,
                    CreditTransactionType::Usage,
                    CreditTransactionType::Adjustment,
                ]);
                $amount = $type === CreditTransactionType::Usage
                    ? -fake()->numberBetween(1, 100)
                    : fake()->numberBetween(10, 500);
                $runningBalance += $amount;

                Credit::factory()->create([
                    'creditable_type' => Organization::class,
                    'creditable_id' => $org->id,
                    'amount' => $amount,
                    'running_balance' => $runningBalance,
                    'type' => $type,
                ]);
            }

            TenantContext::forget();
        }
    }
}

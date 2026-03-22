<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use Illuminate\Database\Seeder;
use Modules\Billing\Models\FailedPaymentAttempt;

final class FailedPaymentAttemptSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::query()->first();

        if ($org === null || FailedPaymentAttempt::query()->where('organization_id', $org->id)->exists()) {
            return;
        }

        FailedPaymentAttempt::query()->create([
            'organization_id' => $org->id,
            'gateway' => 'stripe',
            'gateway_subscription_id' => 'sub_test_123',
            'attempt_number' => 1,
            'dunning_emails_sent' => 0,
            'failed_at' => now(),
        ]);
    }
}

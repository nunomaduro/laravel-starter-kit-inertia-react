<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\Affiliate;
use App\Models\Billing\AffiliatePayout;
use Illuminate\Database\Seeder;

final class AffiliatePayoutSeeder extends Seeder
{
    public function run(): void
    {
        $affiliate = Affiliate::query()->first();

        if ($affiliate === null || AffiliatePayout::query()->where('affiliate_id', $affiliate->id)->exists()) {
            return;
        }

        AffiliatePayout::query()->create([
            'affiliate_id' => $affiliate->id,
            'amount' => 5000,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);
    }
}

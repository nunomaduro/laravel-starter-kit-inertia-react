<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\Affiliate;
use App\Models\User;
use Illuminate\Database\Seeder;

final class AffiliateSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->limit(3)->get();

        foreach ($users as $user) {
            if (Affiliate::query()->where('user_id', $user->id)->exists()) {
                continue;
            }

            Affiliate::query()->create([
                'user_id' => $user->id,
                'status' => 'active',
                'commission_rate' => 0.10,
                'payment_email' => $user->email,
                'total_earnings' => 0,
                'pending_earnings' => 0,
                'paid_earnings' => 0,
                'total_referrals' => 0,
                'successful_conversions' => 0,
                'approved_at' => now(),
            ]);
        }
    }
}

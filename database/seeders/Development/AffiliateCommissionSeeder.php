<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\Affiliate;
use App\Models\Billing\AffiliateCommission;
use App\Models\Billing\Invoice;
use App\Models\Organization;
use Illuminate\Database\Seeder;

final class AffiliateCommissionSeeder extends Seeder
{
    public function run(): void
    {
        $affiliates = Affiliate::query()->limit(2)->get();
        $orgs = Organization::query()->limit(2)->get();
        $invoices = Invoice::query()->limit(2)->get();

        if ($affiliates->isEmpty() || $orgs->isEmpty() || $invoices->isEmpty()) {
            return;
        }

        $affiliate = $affiliates->first();
        $org = $orgs->first();
        $invoice = $invoices->first();

        if (AffiliateCommission::query()->where('affiliate_id', $affiliate->id)->exists()) {
            return;
        }

        AffiliateCommission::query()->create([
            'affiliate_id' => $affiliate->id,
            'referred_organization_id' => $org->id,
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'currency' => 'USD',
            'status' => 'pending',
            'description' => 'Test commission',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\Invoice;
use App\Models\Billing\RefundRequest;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;

final class RefundRequestSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['InvoiceSeeder'];

    public function run(): void
    {
        $invoices = Invoice::query()->withoutGlobalScopes()->limit(5)->get();

        if ($invoices->isEmpty()) {
            return;
        }

        $target = min(3, $invoices->count());

        foreach ($invoices->take($target) as $invoice) {
            $org = $invoice->organization;
            if (! $org) {
                continue;
            }
            TenantContext::set($org);

            RefundRequest::factory()->create([
                'invoice_id' => $invoice->id,
                'amount' => min(fake()->numberBetween(500, 5000), (int) $invoice->total),
            ]);

            TenantContext::forget();
        }
    }
}

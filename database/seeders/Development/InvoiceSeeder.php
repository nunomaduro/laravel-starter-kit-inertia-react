<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\Invoice;
use App\Models\Billing\PaymentGateway;
use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;

final class InvoiceSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder', 'PaymentGatewaySeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(10)->get();
        $gateway = PaymentGateway::query()->where('is_default', true)->first();

        if ($organizations->isEmpty()) {
            return;
        }

        $numberSequence = (int) Invoice::query()->withoutGlobalScopes()->max('id') + 1000;
        $totalTarget = fake()->numberBetween(5, 8);
        $created = 0;

        foreach ($organizations as $org) {
            if ($created >= $totalTarget) {
                break;
            }
            TenantContext::set($org);

            $count = min(2, $totalTarget - $created);
            for ($i = 0; $i < $count; $i++) {
                $isPaid = fake()->boolean(33);
                Invoice::factory()
                    ->when($isPaid, fn ($f) => $f->paid())
                    ->create([
                        'billable_type' => Organization::class,
                        'billable_id' => $org->id,
                        'number' => 'INV-'.now()->format('Ymd').'-'.($numberSequence++),
                        'payment_gateway_id' => $gateway?->id,
                    ]);
                $created++;
            }
        }

        TenantContext::forget();
    }
}

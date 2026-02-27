<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Enums\Billing\CreditTransactionType;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use LemonSqueezy\Laravel\Events\OrderCreated;

final class AddCreditsFromLemonSqueezyOrder
{
    public function handle(OrderCreated $event): void
    {
        $billable = $event->billable;
        if (! $billable instanceof Organization) {
            return;
        }

        $payload = $event->payload;
        $custom = $payload['meta']['custom_data'] ?? [];
        $attributes = $payload['data']['attributes'] ?? [];

        $totalCents = (int) ($attributes['total'] ?? 0);
        if ($totalCents <= 0) {
            return;
        }

        $credits = (int) ($custom['credits'] ?? 0);
        $creditPackId = isset($custom['credit_pack_id']) ? (int) $custom['credit_pack_id'] : null;

        if ($credits <= 0) {
            $credits = $this->creditsFromAmount($totalCents);
        }

        if ($credits <= 0) {
            Log::warning('Lemon Squeezy order received but no credits to add', [
                'organization_id' => $billable->id,
                'order_id' => $payload['data']['id'] ?? null,
            ]);

            return;
        }

        $expiresAt = config('billing.credit_expiration_days')
            ? \Illuminate\Support\Facades\Date::now()->addDays(config('billing.credit_expiration_days'))
            : null;

        $metadata = [
            'lemon_squeezy_order_id' => $payload['data']['id'] ?? null,
            'order_number' => $attributes['order_number'] ?? null,
        ];
        if ($creditPackId) {
            $metadata['credit_pack_id'] = $creditPackId;
        }

        $billable->addCredits(
            $credits,
            CreditTransactionType::Purchase,
            'Lemon Squeezy one-time purchase',
            $expiresAt,
            $metadata
        );
    }

    private function creditsFromAmount(int $totalCents): int
    {
        $centsPerCredit = config('billing.lemon_squeezy_cents_per_credit', 10);

        if ($centsPerCredit <= 0) {
            return 0;
        }

        return (int) floor($totalCents / $centsPerCredit);
    }
}

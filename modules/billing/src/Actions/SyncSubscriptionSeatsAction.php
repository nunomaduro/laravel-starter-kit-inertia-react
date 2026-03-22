<?php

declare(strict_types=1);

namespace Modules\Billing\Actions;

use App\Models\Organization;
use App\Settings\BillingSettings;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Services\PaymentGateway\PaymentGatewayManager;

final readonly class SyncSubscriptionSeatsAction
{
    public function __construct(
        private BillingSettings $billingSettings,
        private PaymentGatewayManager $gateway
    ) {}

    /**
     * Sync subscription quantity with organization member count for per-seat plans.
     */
    public function handle(Organization $organization): void
    {
        if (! $this->billingSettings->enable_seat_based_billing) {
            return;
        }

        $subscription = $organization->activeSubscription();

        if (! $subscription instanceof Subscription || ! $subscription->isPerSeat()) {
            return;
        }

        $memberCount = $organization->members()->count();
        $quantity = max(1, $memberCount);

        if ($subscription->seatCount() === $quantity) {
            return;
        }

        DB::transaction(function () use ($subscription, $quantity): void {
            $subscription->update(['quantity' => $quantity]);

            if ($subscription->gateway_subscription_id) {
                $this->gateway->driver()->updateSubscriptionQuantity(
                    $subscription->gateway_subscription_id,
                    $quantity
                );
            }
        });
    }
}

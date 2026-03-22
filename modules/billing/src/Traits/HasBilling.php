<?php

declare(strict_types=1);

namespace Modules\Billing\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravelcm\Subscriptions\Models\Subscription;
use Modules\Billing\Models\Invoice;

trait HasBilling
{
    /**
     * Active (non-cancelled, not ended) plan subscription.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->planSubscriptions()
            ->where(function ($q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->whereNull('canceled_at')
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Active plan from current subscription.
     */
    public function activePlan(): ?\Laravelcm\Subscriptions\Models\Plan
    {
        $sub = $this->activeSubscription();

        return $sub ? $sub->plan : null;
    }

    public function isOnTrial(): bool
    {
        $sub = $this->activeSubscription();

        return $sub && $sub->trial_ends_at && $sub->trial_ends_at->isFuture();
    }

    public function isSubscribed(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Invoices for this organization (billable = this model).
     *
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'organization_id')->orderByDesc('created_at');
    }

    /**
     * Billing history (invoices).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Invoice>
     */
    public function billingHistory(int $limit = 12)
    {
        return $this->invoices()->limit($limit)->get();
    }
}

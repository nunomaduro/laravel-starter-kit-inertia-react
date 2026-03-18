<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Laravelcm\Subscriptions\Models\Subscription as BaseSubscription;

/**
 * @property-read string|null $gateway_subscription_id
 * @property-read int $quantity
 */
final class Subscription extends BaseSubscription
{
    protected $fillable = [
        'subscriber_id',
        'subscriber_type',
        'plan_id',
        'slug',
        'name',
        'description',
        'gateway_subscription_id',
        'quantity',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at',
    ];

    public function isPerSeat(): bool
    {
        $plan = $this->plan;

        return $plan instanceof Plan && $plan->isPerSeat();
    }

    public function seatCount(): int
    {
        return (int) ($this->quantity ?? 1);
    }

    protected function casts(): array
    {
        return [
            'subscriber_type' => 'string',
            'slug' => 'string',
            'quantity' => 'integer',
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'canceled_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}

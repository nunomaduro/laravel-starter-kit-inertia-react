<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Laravelcm\Subscriptions\Models\Plan as BasePlan;

/**
 * @property-read bool $is_per_seat
 * @property-read float $price_per_seat
 */
final class Plan extends BasePlan
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
        'price',
        'is_per_seat',
        'price_per_seat',
        'signup_fee',
        'currency',
        'trial_period',
        'trial_interval',
        'invoice_period',
        'invoice_interval',
        'grace_period',
        'grace_interval',
        'prorate_day',
        'prorate_period',
        'prorate_extend_due',
        'active_subscribers_limit',
        'sort_order',
    ];

    public function isPerSeat(): bool
    {
        return (bool) $this->is_per_seat;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'float',
            'signup_fee' => 'float',
            'is_per_seat' => 'boolean',
            'price_per_seat' => 'float',
            'deleted_at' => 'datetime',
        ];
    }
}

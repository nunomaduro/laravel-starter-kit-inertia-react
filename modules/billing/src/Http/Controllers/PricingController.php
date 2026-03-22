<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

final readonly class PricingController
{
    public function index(): Response
    {
        $plans = \Laravelcm\Subscriptions\Models\Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($plan): array => [
                'id' => $plan->id,
                'name' => is_array($plan->name) ? (array_first($plan->name) ?? '') : (string) $plan->name,
                'description' => is_array($plan->description) ? (array_first($plan->description) ?? '') : (string) $plan->description,
                'price' => (float) ($plan->price ?? 0),
                'currency' => $plan->currency ?? config('billing.currency'),
                'interval' => $plan->invoice_interval ?? 'month',
            ]);

        return Inertia::render('pricing', [
            'plans' => $plans,
        ]);
    }
}

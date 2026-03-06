<?php

declare(strict_types=1);

namespace App\Jobs\Billing;

use App\Models\Billing\BillingMetric;
use App\Models\Billing\Credit;
use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class GenerateBillingMetrics implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $date = now()->subDay()->toDateString();
        $subscriptionsTable = config('laravel-subscriptions.tables.subscriptions', 'plan_subscriptions');
        $plansTable = config('laravel-subscriptions.tables.plans', 'plans');

        Organization::query()
            ->each(function (Organization $organization) use ($date, $subscriptionsTable, $plansTable): void {
                $orgId = $organization->id;

                $subscriptionQuery = fn () => DB::table($subscriptionsTable)
                    ->where('subscriber_type', Organization::class)
                    ->where('subscriber_id', $orgId);

                $newSubscriptions = $subscriptionQuery()
                    ->whereDate('created_at', $date)
                    ->count();

                $churned = $subscriptionQuery()
                    ->whereDate('canceled_at', $date)
                    ->count();

                $creditsPurchased = (int) Credit::query()
                    ->withoutGlobalScopes()
                    ->where('organization_id', $orgId)
                    ->where('type', 'purchase')
                    ->whereDate('created_at', $date)
                    ->sum('amount');

                $creditsUsed = (int) Credit::query()
                    ->withoutGlobalScopes()
                    ->where('organization_id', $orgId)
                    ->where('type', 'usage')
                    ->whereDate('created_at', $date)
                    ->sum(DB::raw('ABS(amount)'));

                $mrr = (int) $subscriptionQuery()
                    ->join($plansTable, $subscriptionsTable.'.plan_id', '=', $plansTable.'.id')
                    ->whereNull($subscriptionsTable.'.canceled_at')
                    ->whereDate($subscriptionsTable.'.starts_at', '<=', $date)
                    ->where(function ($q) use ($date, $subscriptionsTable): void {
                        $q->whereNull($subscriptionsTable.'.ends_at')
                            ->orWhereDate($subscriptionsTable.'.ends_at', '>', $date);
                    })
                    ->sum($plansTable.'.price');

                $arr = $mrr * 12;

                BillingMetric::query()->updateOrCreate(
                    [
                        'organization_id' => $orgId,
                        'date' => $date,
                    ],
                    [
                        'mrr' => $mrr,
                        'arr' => $arr,
                        'new_subscriptions' => $newSubscriptions,
                        'churned' => $churned,
                        'credits_purchased' => max(0, $creditsPurchased),
                        'credits_used' => $creditsUsed,
                    ]
                );
            });

        Log::info('Billing metrics generated', ['date' => $date]);
    }
}

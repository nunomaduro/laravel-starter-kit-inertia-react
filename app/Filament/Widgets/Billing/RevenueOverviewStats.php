<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Billing;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Models\Billing\Invoice;
use App\Models\Organization;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class RevenueOverviewStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $subscriptionsTable = config('laravel-subscriptions.tables.subscriptions', 'plan_subscriptions');
        $plansTable = config('laravel-subscriptions.tables.plans', 'plans');

        $mrr = $this->calculateMrr($subscriptionsTable, $plansTable);
        $sparkline = $this->getMrrSparkline($subscriptionsTable, $plansTable);
        $mrrTrend = $this->analyzeMrrTrend($sparkline);

        $activeSubscriptions = DB::table($subscriptionsTable)
            ->where('subscriber_type', Organization::class)
            ->whereNull('canceled_at')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->count();

        $lastMonthSubscriptions = DB::table($subscriptionsTable)
            ->where('subscriber_type', Organization::class)
            ->where('created_at', '<', now()->startOfMonth())
            ->whereNull('canceled_at')
            ->count();

        $subscriptionGrowth = $lastMonthSubscriptions > 0
            ? round((($activeSubscriptions - $lastMonthSubscriptions) / $lastMonthSubscriptions) * 100, 1)
            : 0.0;

        $monthlyRevenue = (int) Invoice::query()
            ->withoutGlobalScopes()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()])
            ->sum('total');

        $churnRate = $this->calculateChurnRate($subscriptionsTable);

        $currency = new Currency(config('billing.currency', 'usd'));

        return [
            Stat::make('Monthly Recurring Revenue (MRR)', new Money($mrr, $currency)->format())
                ->description($mrrTrend['description'])
                ->descriptionIcon($mrrTrend['icon'])
                ->chart($sparkline)
                ->color($mrrTrend['color']),

            Stat::make('Active Subscriptions', number_format($activeSubscriptions))
                ->description($subscriptionGrowth >= 0 ? sprintf('+%s%%', $subscriptionGrowth) : $subscriptionGrowth.'%')
                ->descriptionIcon($subscriptionGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($subscriptionGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Revenue This Month', new Money($monthlyRevenue, $currency)->format())
                ->description('vs last month')
                ->descriptionIcon('heroicon-m-banknotes'),

            Stat::make('Churn Rate', $churnRate.'%')
                ->description($this->getChurnDescription($churnRate))
                ->color($this->getChurnColor($churnRate)),
        ];
    }

    private function calculateMrr(string $subscriptionsTable, string $plansTable): int
    {
        $result = DB::table($subscriptionsTable)
            ->join($plansTable, $subscriptionsTable.'.plan_id', '=', $plansTable.'.id')
            ->where($subscriptionsTable.'.subscriber_type', Organization::class)
            ->whereNull($subscriptionsTable.'.canceled_at')
            ->where(fn ($q) => $q->whereNull($subscriptionsTable.'.ends_at')->orWhere($subscriptionsTable.'.ends_at', '>', now()))
            ->sum($plansTable.'.price');

        return (int) round((float) $result * 100);
    }

    private function calculateChurnRate(string $subscriptionsTable): float
    {
        $startOfMonth = now()->startOfMonth();

        $startCount = DB::table($subscriptionsTable)
            ->where('subscriber_type', Organization::class)
            ->where('created_at', '<', $startOfMonth)
            ->whereNull('canceled_at')
            ->count();

        if ($startCount === 0) {
            return 0.0;
        }

        $canceledCount = DB::table($subscriptionsTable)
            ->where('subscriber_type', Organization::class)
            ->whereBetween('canceled_at', [$startOfMonth, now()])
            ->count();

        return round(($canceledCount / $startCount) * 100, 1);
    }

    /** @return array<int> */
    private function getMrrSparkline(string $subscriptionsTable, string $plansTable): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i)->endOfMonth();
            $mrr = DB::table($subscriptionsTable)
                ->join($plansTable, $subscriptionsTable.'.plan_id', '=', $plansTable.'.id')
                ->where($subscriptionsTable.'.subscriber_type', Organization::class)
                ->where($subscriptionsTable.'.created_at', '<=', $date)
                ->where(fn ($q) => $q->whereNull($subscriptionsTable.'.canceled_at')->orWhere($subscriptionsTable.'.canceled_at', '>', $date))
                ->sum($plansTable.'.price');
            $data[] = (int) round((float) $mrr * 100);
        }

        return $data;
    }

    /** @return array{description: string, icon: string, color: string} */
    private function analyzeMrrTrend(array $sparkline): array
    {
        $current = end($sparkline);
        $previous = $sparkline[count($sparkline) - 2] ?? $current;
        $isGrowing = $current >= $previous;

        if ($previous === 0) {
            return [
                'description' => 'New',
                'icon' => 'heroicon-m-arrow-trending-up',
                'color' => 'success',
            ];
        }

        $change = round((($current - $previous) / $previous) * 100, 1);

        return [
            'description' => $change >= 0
                ? sprintf('+%s%% from last month', $change)
                : $change.'% from last month',
            'icon' => $isGrowing ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
            'color' => $isGrowing ? 'success' : 'danger',
        ];
    }

    private function getChurnDescription(float $churnRate): string
    {
        return match (true) {
            $churnRate <= 5 => 'Healthy',
            $churnRate <= 10 => 'Monitor',
            default => 'Action needed',
        };
    }

    private function getChurnColor(float $churnRate): string
    {
        return match (true) {
            $churnRate <= 5 => 'success',
            $churnRate <= 10 => 'warning',
            default => 'danger',
        };
    }
}

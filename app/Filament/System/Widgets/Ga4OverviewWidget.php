<?php

declare(strict_types=1);

namespace App\Filament\System\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\File;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Throwable;

final class Ga4OverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ! empty(config('analytics.property_id'));
    }

    protected function getStats(): array
    {
        $propertyId = config('analytics.property_id');
        $credentialsPath = config('analytics.service_account_credentials_json');

        if (! $propertyId || ! $credentialsPath || ! File::exists($credentialsPath)) {
            return [
                Stat::make('GA4 Traffic', 'Not configured')
                    ->description('Set ANALYTICS_PROPERTY_ID and add service account credentials to view traffic.')
                    ->descriptionIcon('heroicon-m-cog-6-tooth')
                    ->color('gray'),
            ];
        }

        try {
            $totals = Analytics::fetchTotalVisitorsAndPageViews(Period::days(7), 7);
            $totalVisitors = $totals->sum('activeUsers');
            $totalPageViews = $totals->sum('screenPageViews');
            $topPages = Analytics::fetchMostVisitedPages(Period::days(7), 5);
        } catch (Throwable $e) {
            return [
                Stat::make('GA4 Traffic', 'Error')
                    ->description('Check GA4 configuration and credentials.')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        $topPageTitle = $topPages->first()['pageTitle'] ?? '—';

        return [
            Stat::make('Visitors (7 days)', number_format($totalVisitors))
                ->description('Active users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Page views (7 days)', number_format($totalPageViews))
                ->description('Screen page views')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('Top page', $topPageTitle)
                ->description('Most visited (7 days)')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}

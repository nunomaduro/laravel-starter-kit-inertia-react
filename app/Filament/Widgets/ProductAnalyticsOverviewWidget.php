<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Pages\ProductAnalytics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class ProductAnalyticsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        if (! Schema::hasTable('pan_analytics')) {
            return [
                Stat::make('Product analytics', 'Not installed')
                    ->description('Run migrations to enable Pan.')
                    ->url(ProductAnalytics::getUrl()),
            ];
        }

        $totals = DB::table('pan_analytics')
            ->selectRaw('COALESCE(SUM(impressions), 0) as impressions, COALESCE(SUM(hovers), 0) as hovers, COALESCE(SUM(clicks), 0) as clicks')
            ->first();

        $topByClicks = DB::table('pan_analytics')
            ->orderByDesc('clicks')
            ->value('name');

        return [
            Stat::make('Total impressions', number_format((int) $totals->impressions))
                ->description('Elements viewed')
                ->descriptionIcon('heroicon-m-eye')
                ->url(ProductAnalytics::getUrl()),

            Stat::make('Total clicks', number_format((int) $totals->clicks))
                ->description('Clicks tracked')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->url(ProductAnalytics::getUrl()),

            Stat::make('Total hovers', number_format((int) $totals->hovers))
                ->description('Hover events')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->url(ProductAnalytics::getUrl()),

            Stat::make('Top by clicks', $topByClicks ?? '—')
                ->description('Most clicked element')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->url(ProductAnalytics::getUrl()),
        ];
    }
}

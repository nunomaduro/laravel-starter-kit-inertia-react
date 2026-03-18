<?php

declare(strict_types=1);

namespace App\Filament\Pages\Billing\Analytics;

use App\Filament\Widgets\Billing\RevenueOverviewStats;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class RevenueDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?int $navigationSort = 10;

    protected static UnitEnum|string|null $navigationGroup = 'Billing';

    protected static ?string $navigationLabel = 'Revenue Analytics';

    protected static ?string $title = 'Revenue Analytics Dashboard';

    protected static ?string $slug = 'billing/revenue-analytics';

    protected string $view = 'filament.pages.billing.analytics.revenue-dashboard';

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system' && (auth()->user()?->can('access admin panel') ?? false);
    }

    public function getHeaderWidgetsColumns(): int
    {
        return 4;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RevenueOverviewStats::class,
        ];
    }
}

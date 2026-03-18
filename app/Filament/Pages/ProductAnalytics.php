<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductAnalyticsOverviewWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

final class ProductAnalytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 90;

    protected static UnitEnum|string|null $navigationGroup = 'Settings · System';

    protected static ?string $navigationLabel = 'Product Analytics';

    protected static ?string $title = 'Product Analytics';

    protected static ?string $slug = 'analytics/product';

    protected string $view = 'filament.pages.product-analytics';

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system' && (auth()->user()?->can('access admin panel') ?? false);
    }

    public function getHeaderWidgetsColumns(): int
    {
        return 4;
    }

    /**
     * @return Collection<int, object{id: int, name: string, impressions: int, hovers: int, clicks: int}>
     */
    public function getAnalytics(): Collection
    {
        if (! Schema::hasTable('pan_analytics')) {
            return collect();
        }

        return DB::table('pan_analytics')
            ->orderByDesc('clicks')
            ->orderByDesc('impressions')
            ->get();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductAnalyticsOverviewWidget::class,
        ];
    }
}

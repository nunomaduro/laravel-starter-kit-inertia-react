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
use Override;
use UnitEnum;

final class ProductAnalytics extends Page
{
    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    #[Override]
    protected static ?int $navigationSort = 90;

    #[Override]
    protected static UnitEnum|string|null $navigationGroup = 'System';

    #[Override]
    protected static ?string $navigationLabel = 'Product Analytics';

    #[Override]
    protected static ?string $title = 'Product Analytics';

    #[Override]
    protected static ?string $slug = 'analytics/product';

    #[Override]
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

<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\ThemeSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageTheme extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?string $navigationLabel = 'Theme';

    protected static ?int $navigationSort = 30;

    protected static string $settings = ThemeSettings::class;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->isSuperAdmin();
    }

    public function form(Schema $schema): Schema
    {
        $presets = collect(config('theme.presets', []))
            ->mapWithKeys(fn (array $v, string $k): array => [$k => $v['label'] ?? $k])
            ->all();

        return $schema
            ->components([
                Select::make('preset')
                    ->label('Preset')
                    ->options($presets)
                    ->required(),
                Select::make('base_color')
                    ->label('Base color')
                    ->options(config('theme.base_colors', []))
                    ->required(),
                Select::make('radius')
                    ->label('Radius')
                    ->options(config('theme.radii', []))
                    ->required(),
                Select::make('font')
                    ->label('Font')
                    ->options(config('theme.fonts', []))
                    ->required(),
                Select::make('default_appearance')
                    ->label('Default appearance')
                    ->options(config('theme.appearances', []))
                    ->required(),
            ]);
    }
}

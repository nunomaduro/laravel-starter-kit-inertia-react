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
        $presets = collect(config('theme.presets', []))->mapWithKeys(fn (array $v, string $k): array => [$k => $v['label'] ?? $k]);
        $baseColors = collect(config('theme.base_colors', []))->mapWithKeys(fn (string $v, string $k): array => [$k => $v]);
        $radii = collect(config('theme.radii', []))->mapWithKeys(fn (string $v, string $k): array => [$k => $v]);
        $fonts = collect(config('theme.fonts', []))->mapWithKeys(fn (string $v, string $k): array => [$k => $v]);
        $appearances = collect(config('theme.appearances', []))->mapWithKeys(fn (string $v, string $k): array => [$k => $v]);

        return $schema
            ->components([
                Select::make('preset')
                    ->label('Preset')
                    ->options($presets->all())
                    ->required(),
                Select::make('base_color')
                    ->label('Base color')
                    ->options($baseColors->all())
                    ->required(),
                Select::make('radius')
                    ->label('Radius')
                    ->options($radii->all())
                    ->required(),
                Select::make('font')
                    ->label('Font')
                    ->options($fonts->all())
                    ->required(),
                Select::make('default_appearance')
                    ->label('Default appearance')
                    ->options($appearances->all())
                    ->required(),
            ]);
    }
}

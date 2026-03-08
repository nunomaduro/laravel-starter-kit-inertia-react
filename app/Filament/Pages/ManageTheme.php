<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\ThemeSettings;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class ManageTheme extends SettingsPage
{
    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    #[Override]
    protected static ?string $navigationLabel = 'Theme';

    #[Override]
    protected static ?int $navigationSort = 30;

    #[Override]
    protected static string $settings = ThemeSettings::class;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return filament()->getCurrentPanel()?->getId() === 'system' && $user !== null && $user->isSuperAdmin();
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
                Toggle::make('allow_user_theme_customization')
                    ->label('Allow users to customize their own theme')
                    ->helperText('Enabled by default so all users can change appearance. Disable to restrict to organization admins only (org admins can still deny per organization in Settings → Branding).'),
                Toggle::make('allow_user_logo_upload')
                    ->label('Allow users to upload their organization logo')
                    ->helperText('When enabled, any org member who can customize their theme can also upload the organization logo and trigger AI theme suggestions. Disabled by default — only org admins can upload logos.'),
                CheckboxList::make('locked_settings')
                    ->label('Lock settings (orgs cannot override)')
                    ->helperText('Locked settings are shown to org admins but cannot be changed.')
                    ->options([
                        'dark_color_scheme' => 'Dark color scheme',
                        'primary_color' => 'Primary color',
                        'light_color_scheme' => 'Light color scheme',
                        'card_skin' => 'Card skin',
                        'border_radius' => 'Border radius',
                        'sidebar_layout' => 'Sidebar layout',
                        'font' => 'Font',
                        'menu_color' => 'Menu color',
                        'menu_accent' => 'Menu accent',
                    ])
                    ->columns(3),
            ]);
    }
}

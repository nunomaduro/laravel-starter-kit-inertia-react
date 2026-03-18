<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\ThemeSettings;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageTheme extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static ?string $navigationLabel = 'Theme';

    protected static ?int $navigationSort = 30;

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
                Section::make('Core')
                    ->description('Preset, base palette, radius, font, and default light/dark/system appearance.')
                    ->schema([
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
                    ])
                    ->columns(2),
                Section::make('Color schemes')
                    ->description('Dark/light/primary schemes match the in-app theme customizer. Empty uses built-in defaults.')
                    ->schema([
                        Select::make('dark_color_scheme')
                            ->label('Dark color scheme')
                            ->options([
                                '' => 'Default (inherit)',
                                'navy' => 'Navy',
                                'mirage' => 'Mirage',
                                'mint' => 'Mint',
                                'black' => 'Black',
                                'cinder' => 'Cinder',
                            ]),
                        Select::make('primary_color')
                            ->label('Primary color')
                            ->options([
                                '' => 'Default (inherit)',
                                'indigo' => 'Indigo',
                                'blue' => 'Blue',
                                'green' => 'Green',
                                'amber' => 'Amber',
                                'purple' => 'Purple',
                                'rose' => 'Rose',
                            ]),
                        Select::make('light_color_scheme')
                            ->label('Light color scheme')
                            ->options([
                                '' => 'Default (inherit)',
                                'slate' => 'Slate',
                                'gray' => 'Gray',
                                'neutral' => 'Neutral',
                            ]),
                    ])
                    ->columns(2),
                Section::make('Layout & shell')
                    ->schema([
                        Select::make('card_skin')
                            ->label('Card skin')
                            ->options([
                                'shadow' => 'Shadow',
                                'bordered' => 'Bordered',
                                'flat' => 'Flat',
                                'elevated' => 'Elevated',
                            ])
                            ->required(),
                        Select::make('border_radius')
                            ->label('Border radius')
                            ->options([
                                'none' => 'None',
                                'sm' => 'Small',
                                'default' => 'Default',
                                'md' => 'Medium',
                                'lg' => 'Large',
                                'full' => 'Full',
                            ])
                            ->required(),
                        Select::make('sidebar_layout')
                            ->label('Sidebar layout')
                            ->options([
                                'main' => 'Main',
                                'sideblock' => 'Sideblock',
                            ])
                            ->required(),
                        Select::make('menu_color')
                            ->label('Menu color')
                            ->options([
                                'default' => 'Default',
                                'primary' => 'Primary',
                                'muted' => 'Muted',
                            ])
                            ->required(),
                        Select::make('menu_accent')
                            ->label('Menu accent')
                            ->options([
                                'subtle' => 'Subtle',
                                'strong' => 'Strong',
                                'bordered' => 'Bordered',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Permissions')
                    ->schema([
                        Toggle::make('allow_user_theme_customization')
                            ->label('Allow users to customize their own theme')
                            ->helperText('Enabled by default so all users can change appearance. Disable to restrict to organization admins only (org admins can still deny per organization in Settings → Branding).'),
                        Toggle::make('allow_user_logo_upload')
                            ->label('Allow users to upload their organization logo')
                            ->helperText('When enabled, any org member who can customize their theme can also upload the organization logo and trigger AI theme suggestions. Disabled by default — only org admins can upload logos.'),
                    ]),
                Section::make('Organization overrides')
                    ->description('Which settings organization admins cannot override (shown read-only or locked in branding).')
                    ->schema([
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
                    ]),
            ]);
    }
}

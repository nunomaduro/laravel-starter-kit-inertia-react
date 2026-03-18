<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\MonitoringSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageMonitoring extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Monitoring';

    protected static ?int $navigationSort = 70;

    protected static string $settings = MonitoringSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sentry')
                    ->schema([
                        TextInput::make('sentry_dsn')
                            ->label('Sentry DSN')
                            ->password()
                            ->revealable(),
                        TextInput::make('sentry_sample_rate')
                            ->label('Sentry sample rate')
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('sentry_traces_sample_rate')
                            ->label('Sentry traces sample rate')
                            ->numeric()
                            ->step(0.01),
                    ]),
                Section::make('Telescope')
                    ->schema([
                        Toggle::make('telescope_enabled')
                            ->label('Telescope enabled'),
                    ]),
            ]);
    }
}

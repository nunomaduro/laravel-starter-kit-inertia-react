<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\PerformanceSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManagePerformance extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $navigationLabel = 'Performance';

    protected static ?int $navigationSort = 60;

    protected static string $settings = PerformanceSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('cache_enabled')
                    ->label('Cache enabled'),
                TextInput::make('cache_lifetime_seconds')
                    ->label('Cache lifetime seconds')
                    ->numeric(),
                Select::make('cache_driver')
                    ->label('Cache driver')
                    ->options([
                        'file' => 'File',
                        'redis' => 'Redis',
                        'memcached' => 'Memcached',
                        'database' => 'Database',
                    ]),
            ]);
    }
}

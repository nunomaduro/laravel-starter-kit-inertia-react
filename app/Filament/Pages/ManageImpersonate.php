<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\ImpersonateSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageImpersonate extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Features';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Impersonate';

    protected static ?int $navigationSort = 40;

    protected static string $settings = ImpersonateSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('enabled')
                    ->label('Enabled'),
                Select::make('banner_style')
                    ->label('Banner style')
                    ->options([
                        'dark' => 'Dark',
                        'light' => 'Light',
                    ]),
            ]);
    }
}

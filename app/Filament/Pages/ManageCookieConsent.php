<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\CookieConsentSettings;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageCookieConsent extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    protected static ?string $navigationLabel = 'Cookie Consent';

    protected static ?int $navigationSort = 50;

    protected static string $settings = CookieConsentSettings::class;

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
            ]);
    }
}

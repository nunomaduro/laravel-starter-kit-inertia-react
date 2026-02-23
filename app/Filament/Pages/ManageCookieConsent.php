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
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    protected static ?string $navigationLabel = 'Cookie Consent';

    protected static string $settings = CookieConsentSettings::class;

    public static function getNavigationLabel(): string
    {
        return 'Cookie Consent';
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

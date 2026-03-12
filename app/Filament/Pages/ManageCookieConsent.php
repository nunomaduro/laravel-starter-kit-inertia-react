<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\CookieConsentSettings;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class ManageCookieConsent extends SettingsPage
{
    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    #[Override]
    protected static ?string $navigationLabel = 'Cookie Consent';

    #[Override]
    protected static ?int $navigationSort = 50;

    #[Override]
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

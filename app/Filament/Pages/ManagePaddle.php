<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\PaddleSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManagePaddle extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Integrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Paddle';

    protected static ?int $navigationSort = 30;

    protected static string $settings = PaddleSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vendor_id')
                    ->label('Vendor ID')
                    ->password()
                    ->revealable(),
                TextInput::make('vendor_auth_code')
                    ->label('Vendor auth code')
                    ->password()
                    ->revealable(),
                TextInput::make('public_key')
                    ->label('Public key')
                    ->password()
                    ->revealable(),
                TextInput::make('webhook_secret')
                    ->label('Webhook secret')
                    ->password()
                    ->revealable(),
                Toggle::make('sandbox')
                    ->label('Sandbox'),
            ]);
    }
}

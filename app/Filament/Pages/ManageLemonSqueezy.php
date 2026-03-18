<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\LemonSqueezySettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageLemonSqueezy extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Integrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Lemon Squeezy';

    protected static ?int $navigationSort = 40;

    protected static string $settings = LemonSqueezySettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('api_key')
                    ->label('API key')
                    ->password()
                    ->revealable(),
                TextInput::make('signing_secret')
                    ->label('Signing secret')
                    ->password()
                    ->revealable(),
                TextInput::make('store')
                    ->label('Store'),
                TextInput::make('path')
                    ->label('Path'),
                TextInput::make('currency_locale')
                    ->label('Currency locale'),
                TextInput::make('generic_variant_id')
                    ->label('Generic variant ID'),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\StripeSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class ManageStripe extends SettingsPage
{
    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Integrations';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    #[Override]
    protected static ?string $navigationLabel = 'Stripe';

    #[Override]
    protected static ?int $navigationSort = 20;

    #[Override]
    protected static string $settings = StripeSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Key')
                    ->password()
                    ->revealable(),
                TextInput::make('secret')
                    ->label('Secret')
                    ->password()
                    ->revealable(),
                TextInput::make('webhook_secret')
                    ->label('Webhook secret')
                    ->password()
                    ->revealable(),
            ]);
    }
}

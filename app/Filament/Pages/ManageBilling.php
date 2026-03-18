<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\BillingSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageBilling extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Billing';

    protected static ?int $navigationSort = 60;

    protected static string $settings = BillingSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment')
                    ->schema([
                        Select::make('default_gateway')
                            ->label('Default gateway')
                            ->options([
                                'none' => 'None (free app)',
                                'stripe' => 'Stripe',
                                'paddle' => 'Paddle',
                                'lemon_squeezy' => 'Lemon Squeezy',
                                'manual' => 'Manual',
                            ]),
                        TextInput::make('currency')
                            ->label('Currency')
                            ->required(),
                    ]),
                Section::make('Trial & Credits')
                    ->schema([
                        TextInput::make('trial_days')
                            ->label('Trial days')
                            ->numeric(),
                        TextInput::make('credit_expiration_days')
                            ->label('Credit expiration days')
                            ->numeric(),
                    ]),
                Section::make('Dunning')
                    ->schema([
                        TagsInput::make('dunning_intervals')
                            ->label('Dunning intervals'),
                    ]),
                Section::make('Seat Billing')
                    ->schema([
                        Toggle::make('enable_seat_based_billing')
                            ->label('Enable seat based billing'),
                        Toggle::make('allow_multiple_subscriptions')
                            ->label('Allow multiple subscriptions'),
                    ]),
                Section::make('Geo-Restriction')
                    ->schema([
                        Toggle::make('geo_restriction_enabled')
                            ->label('Geo restriction enabled'),
                        TagsInput::make('geo_blocked_countries')
                            ->label('Geo blocked countries'),
                        TagsInput::make('geo_allowed_countries')
                            ->label('Geo allowed countries'),
                    ]),
            ]);
    }
}

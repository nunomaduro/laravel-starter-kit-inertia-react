<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\BroadcastingSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageBroadcasting extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Integrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $navigationLabel = 'Broadcasting';

    protected static ?int $navigationSort = 70;

    protected static string $settings = BroadcastingSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('default_connection')
                    ->label('Default connection')
                    ->options([
                        'reverb' => 'Reverb',
                        'pusher' => 'Pusher',
                        'ably' => 'Ably',
                        'log' => 'Log',
                        'null' => 'Null',
                    ])
                    ->required(),
                TextInput::make('reverb_app_id')
                    ->label('Reverb app ID'),
                TextInput::make('reverb_app_key')
                    ->label('Reverb app key'),
                TextInput::make('reverb_app_secret')
                    ->label('Reverb app secret')
                    ->password()
                    ->revealable(),
                TextInput::make('reverb_host')
                    ->label('Reverb host'),
                TextInput::make('reverb_port')
                    ->label('Reverb port')
                    ->numeric(),
                Select::make('reverb_scheme')
                    ->label('Reverb scheme')
                    ->options([
                        'http' => 'HTTP',
                        'https' => 'HTTPS',
                    ]),
            ]);
    }
}

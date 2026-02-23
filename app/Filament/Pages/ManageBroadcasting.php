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
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $navigationLabel = 'Broadcasting';

    protected static string $settings = BroadcastingSettings::class;

    public static function getNavigationLabel(): string
    {
        return 'Broadcasting';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

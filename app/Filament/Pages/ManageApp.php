<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\AppSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageApp extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'App';

    protected static string $settings = AppSettings::class;

    public static function getNavigationLabel(): string
    {
        return 'App';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('site_name')
                    ->label('Site name')
                    ->required(),
                Toggle::make('maintenance_mode')
                    ->label('Maintenance mode'),
                TextInput::make('timezone')
                    ->label('Timezone')
                    ->required(),
                Select::make('locale')
                    ->label('Locale')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'pt' => 'Portuguese',
                        'it' => 'Italian',
                        'nl' => 'Dutch',
                        'ja' => 'Japanese',
                        'ko' => 'Korean',
                        'zh' => 'Chinese',
                        'ar' => 'Arabic',
                    ])
                    ->required()
                    ->searchable(),
                Select::make('fallback_locale')
                    ->label('Fallback locale')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'pt' => 'Portuguese',
                    ])
                    ->required()
                    ->searchable(),
            ]);
    }
}

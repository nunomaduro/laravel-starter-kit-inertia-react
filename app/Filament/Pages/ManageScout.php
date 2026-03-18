<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\ScoutSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageScout extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Search';

    protected static ?int $navigationSort = 20;

    protected static string $settings = ScoutSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->schema([
                        Select::make('driver')
                            ->label('Driver')
                            ->options([
                                'collection' => 'Collection',
                                'database' => 'Database',
                                'typesense' => 'Typesense',
                                'algolia' => 'Algolia',
                                'meilisearch' => 'Meilisearch',
                                'null' => 'Null',
                            ]),
                        TextInput::make('prefix')
                            ->label('Prefix'),
                        Toggle::make('queue')
                            ->label('Queue'),
                        Toggle::make('identify')
                            ->label('Identify'),
                    ]),
                Section::make('Typesense')
                    ->schema([
                        TextInput::make('typesense_api_key')
                            ->label('Typesense API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('typesense_host')
                            ->label('Typesense host'),
                        TextInput::make('typesense_port')
                            ->label('Typesense port')
                            ->numeric(),
                        Select::make('typesense_protocol')
                            ->label('Typesense protocol')
                            ->options([
                                'http' => 'HTTP',
                                'https' => 'HTTPS',
                            ]),
                    ]),
            ]);
    }
}

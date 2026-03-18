<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\MemorySettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageMemory extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = 'Memory';

    protected static ?int $navigationSort = 80;

    protected static string $settings = MemorySettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('dimensions')
                    ->label('Dimensions')
                    ->numeric(),
                TextInput::make('similarity_threshold')
                    ->label('Similarity threshold')
                    ->numeric()
                    ->step(0.01),
                TextInput::make('recall_limit')
                    ->label('Recall limit')
                    ->numeric(),
                TextInput::make('middleware_recall_limit')
                    ->label('Middleware recall limit')
                    ->numeric(),
                TextInput::make('recall_oversample_factor')
                    ->label('Recall oversample factor')
                    ->numeric(),
                TextInput::make('table')
                    ->label('Table'),
            ]);
    }
}

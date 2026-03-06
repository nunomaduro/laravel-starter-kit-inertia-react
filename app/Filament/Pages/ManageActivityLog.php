<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\ActivityLogSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class ManageActivityLog extends SettingsPage
{
    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Features & Access';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    #[Override]
    protected static ?string $navigationLabel = 'Activity Log Settings';

    #[Override]
    protected static ?int $navigationSort = 50;

    #[Override]
    protected static string $settings = ActivityLogSettings::class;

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
                Toggle::make('delete_records_older_than_days_enabled')
                    ->label('Delete records older than days enabled'),
                TextInput::make('delete_records_older_than_days')
                    ->label('Delete records older than days')
                    ->numeric(),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\BackupSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageBackup extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloudArrowUp;

    protected static ?string $navigationLabel = 'Backup';

    protected static ?int $navigationSort = 10;

    protected static string $settings = BackupSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name'),
                TextInput::make('keep_all_backups_for_days')
                    ->label('Keep all backups for days')
                    ->numeric(),
                TextInput::make('keep_daily_backups_for_days')
                    ->label('Keep daily backups for days')
                    ->numeric(),
                TextInput::make('keep_weekly_backups_for_weeks')
                    ->label('Keep weekly backups for weeks')
                    ->numeric(),
                TextInput::make('keep_monthly_backups_for_months')
                    ->label('Keep monthly backups for months')
                    ->numeric(),
                TextInput::make('keep_yearly_backups_for_years')
                    ->label('Keep yearly backups for years')
                    ->numeric(),
                TextInput::make('delete_oldest_when_size_mb')
                    ->label('Delete oldest when size MB')
                    ->numeric(),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\MediaSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageMedia extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Media';

    protected static ?int $navigationSort = 30;

    protected static string $settings = MediaSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('disk_name')
                    ->label('Disk name')
                    ->options([
                        'local' => 'Local',
                        'public' => 'Public',
                        's3' => 'S3',
                    ]),
                TextInput::make('max_file_size')
                    ->label('Max file size')
                    ->helperText('In kilobytes')
                    ->numeric(),
            ]);
    }
}

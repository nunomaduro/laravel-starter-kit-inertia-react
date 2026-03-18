<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\FilesystemSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageFilesystem extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static ?string $navigationLabel = 'Filesystem';

    protected static ?int $navigationSort = 40;

    protected static string $settings = FilesystemSettings::class;

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
                        Select::make('default_disk')
                            ->label('Default disk')
                            ->options([
                                'local' => 'Local',
                                'public' => 'Public',
                                's3' => 'S3',
                            ]),
                    ]),
                Section::make('S3')
                    ->schema([
                        TextInput::make('s3_key')
                            ->label('S3 key')
                            ->password()
                            ->revealable(),
                        TextInput::make('s3_secret')
                            ->label('S3 secret')
                            ->password()
                            ->revealable(),
                        TextInput::make('s3_region')
                            ->label('S3 region'),
                        TextInput::make('s3_bucket')
                            ->label('S3 bucket'),
                        TextInput::make('s3_url')
                            ->label('S3 URL'),
                    ]),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\PermissionSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class ManagePermissions extends SettingsPage
{
    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Features & Access';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    #[Override]
    protected static ?string $navigationLabel = 'Permission Settings';

    #[Override]
    protected static ?int $navigationSort = 30;

    #[Override]
    protected static string $settings = PermissionSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('teams_enabled')
                    ->label('Teams enabled'),
                TextInput::make('team_foreign_key')
                    ->label('Team foreign key'),
            ]);
    }
}

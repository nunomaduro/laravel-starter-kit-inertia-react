<?php

declare(strict_types=1);

namespace App\Filament\System\Resources\Permissions;

use App\Filament\System\Resources\Permissions\Pages\ListPermissions;
use App\Filament\System\Resources\Permissions\Pages\ViewPermission;
use App\Filament\System\Resources\Permissions\Tables\PermissionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use UnitEnum;

final class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 30;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
    }

    /**
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'guard_name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'view' => ViewPermission::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

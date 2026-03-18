<?php

declare(strict_types=1);

namespace App\Filament\Resources\CreditPacks;

use App\Filament\Resources\CreditPacks\Pages\CreateCreditPack;
use App\Filament\Resources\CreditPacks\Pages\EditCreditPack;
use App\Filament\Resources\CreditPacks\Pages\ListCreditPacks;
use App\Filament\Resources\CreditPacks\Schemas\CreditPackForm;
use App\Filament\Resources\CreditPacks\Tables\CreditPacksTable;
use App\Models\Billing\CreditPack;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class CreditPackResource extends Resource
{
    protected static ?string $model = CreditPack::class;

    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 20;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Schema $schema): Schema
    {
        return CreditPackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CreditPacksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreditPacks::route('/'),
            'create' => CreateCreditPack::route('/create'),
            'edit' => EditCreditPack::route('/{record}/edit'),
        ];
    }
}

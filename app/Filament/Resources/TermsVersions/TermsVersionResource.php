<?php

declare(strict_types=1);

namespace App\Filament\Resources\TermsVersions;

use App\Filament\Resources\TermsVersions\Pages\CreateTermsVersion;
use App\Filament\Resources\TermsVersions\Pages\EditTermsVersion;
use App\Filament\Resources\TermsVersions\Pages\ListTermsVersions;
use App\Filament\Resources\TermsVersions\Schemas\TermsVersionForm;
use App\Filament\Resources\TermsVersions\Tables\TermsVersionsTable;
use App\Models\TermsVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class TermsVersionResource extends Resource
{
    #[Override]
    protected static ?string $model = TermsVersion::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    #[Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    protected static ?string $navigationLabel = 'Terms & Privacy';

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Content & Legal';

    #[Override]
    protected static ?int $navigationSort = 20;

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system' && (auth()->user()?->hasRole('super-admin') ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return TermsVersionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TermsVersionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTermsVersions::route('/'),
            'create' => CreateTermsVersion::route('/create'),
            'edit' => EditTermsVersion::route('/{record}/edit'),
        ];
    }
}

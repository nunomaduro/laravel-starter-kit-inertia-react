<?php

declare(strict_types=1);

namespace App\Filament\Resources\ChangelogEntries;

use App\Features\ChangelogFeature;
use App\Filament\Resources\ChangelogEntries\Pages\CreateChangelogEntry;
use App\Filament\Resources\ChangelogEntries\Pages\EditChangelogEntry;
use App\Filament\Resources\ChangelogEntries\Pages\ListChangelogEntries;
use App\Filament\Resources\ChangelogEntries\Pages\ViewChangelogEntry;
use App\Filament\Resources\ChangelogEntries\Schemas\ChangelogEntryForm;
use App\Filament\Resources\ChangelogEntries\Schemas\ChangelogEntryInfolist;
use App\Filament\Resources\ChangelogEntries\Tables\ChangelogEntriesTable;
use App\Models\ChangelogEntry;
use App\Support\FeatureHelper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

final class ChangelogEntryResource extends Resource
{
    protected static ?string $model = ChangelogEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 20;

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'version'];
    }

    public static function form(Schema $schema): Schema
    {
        return ChangelogEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChangelogEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChangelogEntriesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && filament()->getCurrentPanel()?->getId() === 'admin' && FeatureHelper::isActiveForClass(ChangelogFeature::class, $user) && parent::canAccess();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChangelogEntries::route('/'),
            'create' => CreateChangelogEntry::route('/create'),
            'view' => ViewChangelogEntry::route('/{record}'),
            'edit' => EditChangelogEntry::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

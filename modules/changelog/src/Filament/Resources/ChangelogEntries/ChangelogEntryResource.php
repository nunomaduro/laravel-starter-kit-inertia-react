<?php

declare(strict_types=1);

namespace Modules\Changelog\Filament\Resources\ChangelogEntries;

use App\Support\FeatureHelper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Changelog\Features\ChangelogFeature;
use Modules\Changelog\Filament\Resources\ChangelogEntries\Pages\CreateChangelogEntry;
use Modules\Changelog\Filament\Resources\ChangelogEntries\Pages\EditChangelogEntry;
use Modules\Changelog\Filament\Resources\ChangelogEntries\Pages\ListChangelogEntries;
use Modules\Changelog\Filament\Resources\ChangelogEntries\Pages\ViewChangelogEntry;
use Modules\Changelog\Filament\Resources\ChangelogEntries\Schemas\ChangelogEntryForm;
use Modules\Changelog\Filament\Resources\ChangelogEntries\Schemas\ChangelogEntryInfolist;
use Modules\Changelog\Filament\Resources\ChangelogEntries\Tables\ChangelogEntriesTable;
use Modules\Changelog\Models\ChangelogEntry;
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

<?php

declare(strict_types=1);

namespace Modules\Help\Filament\Resources\HelpArticles;

use App\Support\FeatureHelper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Help\Features\HelpFeature;
use Modules\Help\Filament\Resources\HelpArticles\Pages\CreateHelpArticle;
use Modules\Help\Filament\Resources\HelpArticles\Pages\EditHelpArticle;
use Modules\Help\Filament\Resources\HelpArticles\Pages\ListHelpArticles;
use Modules\Help\Filament\Resources\HelpArticles\Pages\ViewHelpArticle;
use Modules\Help\Filament\Resources\HelpArticles\Schemas\HelpArticleForm;
use Modules\Help\Filament\Resources\HelpArticles\Schemas\HelpArticleInfolist;
use Modules\Help\Filament\Resources\HelpArticles\Tables\HelpArticlesTable;
use Modules\Help\Models\HelpArticle;
use UnitEnum;

final class HelpArticleResource extends Resource
{
    protected static ?string $model = HelpArticle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 30;

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'excerpt'];
    }

    public static function form(Schema $schema): Schema
    {
        return HelpArticleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HelpArticleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpArticlesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && filament()->getCurrentPanel()?->getId() === 'admin' && FeatureHelper::isActiveForClass(HelpFeature::class, $user) && parent::canAccess();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpArticles::route('/'),
            'create' => CreateHelpArticle::route('/create'),
            'view' => ViewHelpArticle::route('/{record}'),
            'edit' => EditHelpArticle::route('/{record}/edit'),
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

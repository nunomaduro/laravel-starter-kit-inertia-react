<?php

declare(strict_types=1);

namespace Modules\Blog\Filament\Resources\Posts;

use App\Support\FeatureHelper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Blog\Features\BlogFeature;
use Modules\Blog\Filament\Resources\Posts\Pages\CreatePost;
use Modules\Blog\Filament\Resources\Posts\Pages\EditPost;
use Modules\Blog\Filament\Resources\Posts\Pages\ListPosts;
use Modules\Blog\Filament\Resources\Posts\Pages\ViewPost;
use Modules\Blog\Filament\Resources\Posts\Schemas\PostForm;
use Modules\Blog\Filament\Resources\Posts\Schemas\PostInfolist;
use Modules\Blog\Filament\Resources\Posts\Tables\PostsTable;
use Modules\Blog\Models\Post;
use UnitEnum;

final class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 10;

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'excerpt'];
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PostInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'view' => ViewPost::route('/{record}'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && filament()->getCurrentPanel()?->getId() === 'admin' && FeatureHelper::isActiveForClass(BlogFeature::class, $user) && parent::canAccess();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

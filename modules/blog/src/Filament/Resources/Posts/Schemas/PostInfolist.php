<?php

declare(strict_types=1);

namespace Modules\Blog\Filament\Resources\Posts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('author.name')->label('Author'),
                TextEntry::make('excerpt')->columnSpanFull()->placeholder('-'),
                TextEntry::make('content')->columnSpanFull()->placeholder('-'),
                IconEntry::make('is_published')->label('Published')->boolean(),
                TextEntry::make('published_at')->dateTime()->placeholder('-'),
                TextEntry::make('views')->numeric()->placeholder('-'),
                TextEntry::make('tags.name')->label('Tags')->badge(),
                TextEntry::make('meta_title')->placeholder('-'),
                TextEntry::make('meta_description')->placeholder('-')->columnSpanFull(),
                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
            ]);
    }
}

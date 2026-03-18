<?php

declare(strict_types=1);

namespace Modules\Help\Filament\Resources\HelpArticles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Modules\Help\Models\HelpArticle;

final class HelpArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('slug')->placeholder('-'),
                TextEntry::make('excerpt')->placeholder('-')->columnSpanFull(),
                TextEntry::make('content')->columnSpanFull()->placeholder('-'),
                TextEntry::make('category')->placeholder('-'),
                TextEntry::make('views')->numeric()->placeholder('-'),
                TextEntry::make('helpful_count')->numeric()->placeholder('-'),
                TextEntry::make('not_helpful_count')->numeric()->placeholder('-'),
                TextEntry::make('order')->numeric()->placeholder('-'),
                IconEntry::make('is_published')->label('Published')->boolean(),
                IconEntry::make('featured_flag')
                    ->label('Featured')
                    ->getStateUsing(fn (HelpArticle $record): bool => $record->hasFlag('featured'))
                    ->boolean(),
                IconEntry::make('pinned_flag')
                    ->label('Pinned')
                    ->getStateUsing(fn (HelpArticle $record): bool => $record->hasFlag('pinned'))
                    ->boolean(),
                TextEntry::make('tags.name')->label('Tags')->badge(),
                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->visible(fn (HelpArticle $record): bool => $record->trashed()),
            ]);
    }
}

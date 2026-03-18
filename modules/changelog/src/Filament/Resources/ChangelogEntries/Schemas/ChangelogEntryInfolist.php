<?php

declare(strict_types=1);

namespace Modules\Changelog\Filament\Resources\ChangelogEntries\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class ChangelogEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('description')->columnSpanFull()->placeholder('-'),
                TextEntry::make('version')->placeholder('-'),
                TextEntry::make('type')->badge()->placeholder('-'),
                TextEntry::make('tags.name')->label('Tags')->badge(),
                IconEntry::make('is_published')->label('Published')->boolean(),
                TextEntry::make('released_at')->dateTime()->placeholder('-'),
                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
            ]);
    }
}

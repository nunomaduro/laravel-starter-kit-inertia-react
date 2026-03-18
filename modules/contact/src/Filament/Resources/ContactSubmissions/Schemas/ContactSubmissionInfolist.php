<?php

declare(strict_types=1);

namespace Modules\Contact\Filament\Resources\ContactSubmissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class ContactSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('subject'),
                TextEntry::make('status')->badge(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('message')->columnSpanFull(),
            ]);
    }
}

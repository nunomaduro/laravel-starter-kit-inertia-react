<?php

declare(strict_types=1);

namespace Modules\Contact\Filament\Resources\ContactSubmissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

final class ContactSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options([
                        'new' => 'New',
                        'read' => 'Read',
                        'replied' => 'Replied',
                    ])
                    ->required(),
            ]);
    }
}

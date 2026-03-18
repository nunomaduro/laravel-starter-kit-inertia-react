<?php

declare(strict_types=1);

namespace Modules\Contact\Filament\Resources\ContactSubmissions\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Contact\Filament\Resources\ContactSubmissions\ContactSubmissionResource;

final class ListContactSubmissions extends ListRecords
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

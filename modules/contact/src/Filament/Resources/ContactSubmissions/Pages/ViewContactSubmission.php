<?php

declare(strict_types=1);

namespace Modules\Contact\Filament\Resources\ContactSubmissions\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Contact\Filament\Resources\ContactSubmissions\ContactSubmissionResource;

final class ViewContactSubmission extends ViewRecord
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

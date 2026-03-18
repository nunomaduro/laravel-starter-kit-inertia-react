<?php

declare(strict_types=1);

namespace Modules\Contact\Filament\Resources\ContactSubmissions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Contact\Filament\Resources\ContactSubmissions\ContactSubmissionResource;

final class EditContactSubmission extends EditRecord
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

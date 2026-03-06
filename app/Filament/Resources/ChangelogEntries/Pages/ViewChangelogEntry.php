<?php

declare(strict_types=1);

namespace App\Filament\Resources\ChangelogEntries\Pages;

use App\Filament\Resources\ChangelogEntries\ChangelogEntryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewChangelogEntry extends ViewRecord
{
    #[Override]
    protected static string $resource = ChangelogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

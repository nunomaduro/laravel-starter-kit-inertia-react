<?php

declare(strict_types=1);

namespace Modules\Changelog\Filament\Resources\ChangelogEntries\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Changelog\Filament\Resources\ChangelogEntries\ChangelogEntryResource;

final class ViewChangelogEntry extends ViewRecord
{
    protected static string $resource = ChangelogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

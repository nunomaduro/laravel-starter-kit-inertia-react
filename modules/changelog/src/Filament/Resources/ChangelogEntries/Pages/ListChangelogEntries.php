<?php

declare(strict_types=1);

namespace Modules\Changelog\Filament\Resources\ChangelogEntries\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Changelog\Filament\Resources\ChangelogEntries\ChangelogEntryResource;

final class ListChangelogEntries extends ListRecords
{
    protected static string $resource = ChangelogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

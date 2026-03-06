<?php

declare(strict_types=1);

namespace App\Filament\Resources\ChangelogEntries\Pages;

use App\Filament\Resources\ChangelogEntries\ChangelogEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListChangelogEntries extends ListRecords
{
    #[Override]
    protected static string $resource = ChangelogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

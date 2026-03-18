<?php

declare(strict_types=1);

namespace Modules\Announcements\Filament\Resources\Announcements\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Announcements\Filament\Resources\Announcements\AnnouncementResource;

final class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

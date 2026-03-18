<?php

declare(strict_types=1);

namespace Modules\Announcements\Filament\Resources\Announcements\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Announcements\Filament\Resources\Announcements\AnnouncementResource;

final class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

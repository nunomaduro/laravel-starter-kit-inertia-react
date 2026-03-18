<?php

declare(strict_types=1);

namespace App\Filament\Resources\Announcements\Pages;

use App\Enums\AnnouncementScope;
use App\Filament\Resources\Announcements\AnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $data['created_by'] = $user?->id;

        if (! $user?->can('announcements.manage_global')) {
            $data['scope'] = AnnouncementScope::Organization->value;
            $data['organization_id'] = tenant_id();
        }

        return $data;
    }
}

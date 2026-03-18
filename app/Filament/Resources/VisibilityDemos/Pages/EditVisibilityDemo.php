<?php

declare(strict_types=1);

namespace App\Filament\Resources\VisibilityDemos\Pages;

use App\Enums\VisibilityEnum;
use App\Filament\Resources\VisibilityDemos\VisibilityDemoResource;
use Filament\Resources\Pages\EditRecord;

final class EditVisibilityDemo extends EditRecord
{
    protected static string $resource = VisibilityDemoResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['share_to_all_orgs'] = $this->record->isGlobal();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $shareToAll = (bool) ($data['share_to_all_orgs'] ?? false);
        unset($data['share_to_all_orgs']);

        if ($shareToAll && auth()->user()?->isSuperAdmin()) {
            $this->record->visibility = VisibilityEnum::Global;
            $this->record->organization_id = null;
        }

        return $data;
    }
}

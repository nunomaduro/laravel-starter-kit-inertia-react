<?php

declare(strict_types=1);

namespace App\Filament\Resources\VisibilityDemos\Pages;

use App\Enums\VisibilityEnum;
use App\Filament\Resources\VisibilityDemos\VisibilityDemoResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateVisibilityDemo extends CreateRecord
{
    protected static string $resource = VisibilityDemoResource::class;

    private bool $shareToAllOrgs = false;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->shareToAllOrgs = (bool) ($data['share_to_all_orgs'] ?? config('tenancy.super_admin.default_share_new_to_all_orgs', true));
        unset($data['share_to_all_orgs']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->shareToAllOrgs && $this->record->exists) {
            $this->record->visibility = VisibilityEnum::Global;
            $this->record->organization_id = null;
            $this->record->save();
        }
    }
}

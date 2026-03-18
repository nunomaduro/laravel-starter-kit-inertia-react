<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use App\Services\ActivityLogRbac;
use Filament\Resources\Pages\CreateRecord;

final class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'permissions_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->permissions()->sync(RoleForm::mergePermissionIds($this->form->getState()));
        $this->record->load('permissions');
        resolve(ActivityLogRbac::class)->logPermissionsAssigned(
            $this->record,
            ActivityLogRbac::permissionNamesFrom($this->record)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use App\Services\ActivityLogRbac;
use App\Services\PermissionCategoryResolver;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /**
     * @var array<int, string>
     */
    private array $previousPermissionNames = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->form->fill(array_merge($this->form->getState(), $this->getRoleFormStateFromRecord()));
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'permissions_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->previousPermissionNames = ActivityLogRbac::permissionNamesFrom($this->record);
    }

    protected function afterSave(): void
    {
        $this->record->permissions()->sync(RoleForm::mergePermissionIds($this->form->getState()));
        $this->record->load('permissions');
        resolve(ActivityLogRbac::class)->logPermissionsUpdated(
            $this->record,
            $this->previousPermissionNames,
            ActivityLogRbac::permissionNamesFrom($this->record)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getRoleFormStateFromRecord(): array
    {
        $resolver = resolve(PermissionCategoryResolver::class);
        $grouped = $resolver->getPermissionsGroupedByCategory();
        $rolePermissionIds = $this->record->permissions->pluck('id')->all();
        $state = [];

        foreach ($grouped as $categoryKey => $options) {
            $fieldName = 'permissions_'.$categoryKey;
            $state[$fieldName] = array_values(array_intersect(array_keys($options), $rolePermissionIds));
        }

        return $state;
    }
}

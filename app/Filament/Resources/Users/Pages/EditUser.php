<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\ActivityLogRbac;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use STS\FilamentImpersonate\Actions\Impersonate;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @var array<int, string>
     */
    private array $previousRoleNames = [];

    /**
     * @var list<string>
     */
    private array $pendingTagNames = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function mutateFormDataBeforeFill(array $data): array
    {
        $data['tag_names'] = $this->getRecord()->tags->pluck('name')->values()->all();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Impersonate::make()
                ->record($this->getRecord())
                ->visible(fn (): bool => auth()->user()?->canImpersonate() === true),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingTagNames = array_values(array_filter(
            is_array($data['tag_names'] ?? null) ? $data['tag_names'] : [],
            fn ($v): bool => is_string($v) && $v !== ''
        ));
        unset($data['tag_names']);

        $user = $this->getRecord();
        if (! $user->isLastSuperAdmin() || ! $user->hasRole('super-admin')) {
            return $data;
        }

        $superAdminRole = Role::query()->where('name', 'super-admin')->first();
        if ($superAdminRole === null) {
            return $data;
        }

        $newRoleIds = $data['roles'] ?? [];
        $hasSuperAdmin = is_array($newRoleIds) && in_array($superAdminRole->getKey(), $newRoleIds, true);
        if (! $hasSuperAdmin) {
            throw ValidationException::withMessages([
                'roles' => ['Cannot remove the super-admin role from the last super-admin user.'],
            ]);
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->previousRoleNames = ActivityLogRbac::roleNamesFrom($this->record);
    }

    protected function afterSave(): void
    {
        $this->record->syncTags($this->pendingTagNames);
        $this->record->load('roles');

        resolve(ActivityLogRbac::class)->logRolesUpdated(
            $this->record,
            $this->previousRoleNames,
            ActivityLogRbac::roleNamesFrom($this->record)
        );
    }
}

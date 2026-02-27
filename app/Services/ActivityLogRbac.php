<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final readonly class ActivityLogRbac
{
    /**
     * @return array<int, string>
     */
    public static function roleNamesFrom(Model $user): array
    {
        return $user->roles->pluck('name')->values()->all();
    }

    /**
     * @return array<int, string>
     */
    public static function permissionNamesFrom(Model $role): array
    {
        return $role->permissions->pluck('name')->values()->all();
    }

    public function logRolesUpdated(Model $user, array $oldRoleNames, array $newRoleNames): void
    {
        if ($oldRoleNames === $newRoleNames) {
            return;
        }

        $this->logActivity($user, ActivityType::RolesUpdated, [
            'old' => $oldRoleNames,
            'attributes' => $newRoleNames,
        ]);
    }

    public function logRolesAssigned(Model $user, array $roleNames): void
    {
        if ($roleNames === []) {
            return;
        }

        $this->logActivity($user, ActivityType::RolesAssigned, ['attributes' => $roleNames]);
    }

    public function logPermissionsUpdated(Model $role, array $oldPermissionNames, array $newPermissionNames): void
    {
        if ($oldPermissionNames === $newPermissionNames) {
            return;
        }

        $this->logActivity($role, ActivityType::PermissionsUpdated, [
            'old' => $oldPermissionNames,
            'attributes' => $newPermissionNames,
        ]);
    }

    public function logPermissionsAssigned(Model $role, array $permissionNames): void
    {
        if ($permissionNames === []) {
            return;
        }

        $this->logActivity($role, ActivityType::PermissionsAssigned, ['attributes' => $permissionNames]);
    }

    private function logActivity(Model $subject, ActivityType $type, array $properties): void
    {
        $causer = auth()->user();
        if (! $causer instanceof Authenticatable) {
            return;
        }

        activity()
            ->performedOn($subject)
            ->causedBy($causer)
            ->withProperties($properties)
            ->log($type->value);
    }
}

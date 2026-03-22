<?php

declare(strict_types=1);

namespace App\Services\Permission;

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

use function setPermissionsTeamId;

/**
 * Central service for organization-scoped permission checks.
 *
 * Uses Spatie team-scoped roles with organization_id as team.
 * Owner and super-admin implicitly have all org permissions.
 */
final class PermissionService
{
    public function canInOrganization(User $user, string $permission, ?Organization $organization = null): bool
    {
        $organization ??= TenantContext::organization();

        if (! $organization instanceof Organization) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($organization->owner_id === $user->id) {
            return true;
        }

        $previous = TenantContext::get();
        try {
            TenantContext::set($organization);
            resolve(PermissionRegistrar::class)->setPermissionsTeamId($organization->id);

            return $user->hasPermissionTo($permission);
        } finally {
            TenantContext::set($previous);
            setPermissionsTeamId($previous?->id);
        }
    }

    /**
     * @param  array<string>  $permissions
     */
    public function canAnyInOrganization(User $user, array $permissions, ?Organization $organization = null): bool
    {
        return array_any($permissions, fn (string $permission): bool => $this->canInOrganization($user, $permission, $organization));
    }

    /**
     * @param  array<string>  $permissions
     */
    public function canAllInOrganization(User $user, array $permissions, ?Organization $organization = null): bool
    {
        return array_all($permissions, fn (string $permission): bool => $this->canInOrganization($user, $permission, $organization));
    }

    /**
     * @return Collection<int, string>
     */
    public function getOrganizationPermissions(User $user, ?Organization $organization = null): Collection
    {
        $organization ??= TenantContext::organization();

        if (! $organization instanceof Organization) {
            return collect();
        }

        if ($user->isSuperAdmin() || $organization->owner_id === $user->id) {
            return $this->getAllOrganizationPermissions();
        }

        $previous = TenantContext::get();
        try {
            TenantContext::set($organization);
            resolve(PermissionRegistrar::class)->setPermissionsTeamId($organization->id);

            return $user->getAllPermissions()->pluck('name');
        } finally {
            TenantContext::set($previous);
            setPermissionsTeamId($previous?->id);
        }
    }

    /**
     * @return Collection<int, string>
     */
    public function getAllOrganizationPermissions(): Collection
    {
        return Permission::query()
            ->where('name', 'like', 'org.%')
            ->orderBy('name')
            ->pluck('name');
    }

    public function clearCache(): void
    {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

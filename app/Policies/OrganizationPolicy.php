<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

use function getPermissionsTeamId;
use function setPermissionsTeamId;

final class OrganizationPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->belongsToOrganization($organization->id) || $user->isSuperAdmin();
    }

    public function create(): bool
    {
        return config('tenancy.enabled', true)
            && config('tenancy.allow_user_organization_creation', true);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $this->isOwnerSuperAdminOrRole($user, $organization, 'admin');
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $organization->isOwner($user) || $user->isSuperAdmin();
    }

    public function restore(User $user, Organization $organization): bool
    {
        return $organization->isOwner($user) || $user->isSuperAdmin();
    }

    public function forceDelete(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function addMember(User $user, Organization $organization): bool
    {
        return $this->isOwnerSuperAdminOrRole($user, $organization, 'admin');
    }

    private function isOwnerSuperAdminOrRole(User $user, Organization $organization, string $role): bool
    {
        return $organization->isOwner($user)
            || $user->isSuperAdmin()
            || $this->userHasOrgRole($user, $organization, $role);
    }

    private function userHasOrgRole(User $user, Organization $organization, string $role): bool
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($organization->id);
        try {
            return $user->hasRole($role);
        } finally {
            setPermissionsTeamId($previousTeamId);
        }
    }
}

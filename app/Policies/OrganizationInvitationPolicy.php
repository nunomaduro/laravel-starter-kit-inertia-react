<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;

use function getPermissionsTeamId;
use function setPermissionsTeamId;

final class OrganizationInvitationPolicy
{
    public function viewAny(User $user, ?Organization $organization = null): bool
    {
        if (! $organization instanceof Organization) {
            return $user->isSuperAdmin() || $user->organizations()->exists();
        }

        return $user->belongsToOrganization($organization->id);
    }

    public function view(User $user, OrganizationInvitation $organizationInvitation): bool
    {
        return $user->belongsToOrganization($organizationInvitation->organization_id);
    }

    public function create(User $user, Organization $organization): bool
    {
        return $this->isOwnerSuperAdminOrRole($user, $organization, 'admin');
    }

    public function update(User $user, OrganizationInvitation $organizationInvitation): bool
    {
        if ($organizationInvitation->invited_by === $user->id || $user->isSuperAdmin()) {
            return true;
        }

        return $this->isOwnerSuperAdminOrRole($user, $organizationInvitation->organization, 'admin');
    }

    public function delete(User $user, OrganizationInvitation $organizationInvitation): bool
    {
        return $this->update($user, $organizationInvitation);
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

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OrganizationDomain;
use App\Models\User;

final class OrganizationDomainPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canInOrganization('org.settings.manage');
    }

    public function view(User $user, OrganizationDomain $domain): bool
    {
        return $user->canInOrganization('org.settings.manage', $domain->organization);
    }

    public function create(User $user): bool
    {
        return $user->canInOrganization('org.settings.manage');
    }

    public function update(User $user, OrganizationDomain $domain): bool
    {
        return $user->canInOrganization('org.settings.manage', $domain->organization);
    }

    public function delete(User $user, OrganizationDomain $domain): bool
    {
        return $user->canInOrganization('org.settings.manage', $domain->organization);
    }

    public function restore(User $user, OrganizationDomain $domain): bool
    {
        return $user->canInOrganization('org.settings.manage', $domain->organization);
    }

    public function forceDelete(User $user, OrganizationDomain $domain): bool
    {
        return $user->canInOrganization('org.settings.manage', $domain->organization);
    }
}

<?php

declare(strict_types=1);

namespace Cogneiss\ModuleCrm\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Cogneiss\ModuleCrm\Models\Deal;

final class DealPolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.crm.view');
    }

    public function view(User $user, Deal $deal): bool
    {
        return $user->canInOrganization('org.crm.view', $deal->organization);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.crm.manage');
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->canInOrganization('org.crm.manage', $deal->organization);
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $user->canInOrganization('org.crm.manage', $deal->organization);
    }

    public function restore(User $user, Deal $deal): bool
    {
        return $user->canInOrganization('org.crm.manage', $deal->organization);
    }

    public function forceDelete(User $user, Deal $deal): bool
    {
        return $user->canInOrganization('org.crm.manage', $deal->organization);
    }
}

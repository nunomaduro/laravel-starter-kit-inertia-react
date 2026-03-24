<?php

declare(strict_types=1);

namespace Modules\Crm\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Crm\Models\Pipeline;

final class PipelinePolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.crm.view');
    }

    public function view(User $user, Pipeline $pipeline): bool
    {
        return $user->canInOrganization('org.crm.view', $pipeline->organization);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.crm.manage');
    }

    public function update(User $user, Pipeline $pipeline): bool
    {
        return $user->canInOrganization('org.crm.manage', $pipeline->organization);
    }

    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $user->canInOrganization('org.crm.manage', $pipeline->organization);
    }

    public function restore(User $user, Pipeline $pipeline): bool
    {
        return $user->canInOrganization('org.crm.manage', $pipeline->organization);
    }

    public function forceDelete(User $user, Pipeline $pipeline): bool
    {
        return $user->canInOrganization('org.crm.manage', $pipeline->organization);
    }
}

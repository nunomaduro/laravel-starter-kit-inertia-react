<?php

declare(strict_types=1);

namespace Modules\Dashboards\Policies;

use App\Models\User;
use Modules\Dashboards\Models\Dashboard;

final class DashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canInOrganization('org.dashboards.manage');
    }

    public function view(User $user, Dashboard $dashboard): bool
    {
        return $user->canInOrganization('org.dashboards.manage', $dashboard->organization);
    }

    public function create(User $user): bool
    {
        return $user->canInOrganization('org.dashboards.manage');
    }

    public function update(User $user, Dashboard $dashboard): bool
    {
        return $user->canInOrganization('org.dashboards.manage', $dashboard->organization);
    }

    public function delete(User $user, Dashboard $dashboard): bool
    {
        return $user->canInOrganization('org.dashboards.manage', $dashboard->organization);
    }

    public function setDefault(User $user, Dashboard $dashboard): bool
    {
        return $user->canInOrganization('org.dashboards.manage', $dashboard->organization);
    }
}

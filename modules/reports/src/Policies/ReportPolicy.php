<?php

declare(strict_types=1);

namespace Modules\Reports\Policies;

use App\Models\User;
use Modules\Reports\Models\Report;

final class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canInOrganization('org.reports.manage');
    }

    public function view(User $user, Report $report): bool
    {
        return $user->canInOrganization('org.reports.manage', $report->organization);
    }

    public function create(User $user): bool
    {
        return $user->canInOrganization('org.reports.manage');
    }

    public function update(User $user, Report $report): bool
    {
        return $user->canInOrganization('org.reports.manage', $report->organization);
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->canInOrganization('org.reports.manage', $report->organization);
    }

    public function export(User $user, Report $report): bool
    {
        return $user->canInOrganization('org.reports.manage', $report->organization);
    }
}

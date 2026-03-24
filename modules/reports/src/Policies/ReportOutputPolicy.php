<?php

declare(strict_types=1);

namespace Modules\Reports\Policies;

use App\Models\User;
use Modules\Reports\Models\ReportOutput;

final class ReportOutputPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canInOrganization('org.reports.manage');
    }

    public function view(User $user, ReportOutput $output): bool
    {
        return $user->canInOrganization('org.reports.manage', $output->report?->organization);
    }

    public function create(User $user): bool
    {
        return $user->canInOrganization('org.reports.manage');
    }

    public function update(User $user, ReportOutput $output): bool
    {
        return $user->canInOrganization('org.reports.manage', $output->report?->organization);
    }

    public function delete(User $user, ReportOutput $output): bool
    {
        return $user->canInOrganization('org.reports.manage', $output->report?->organization);
    }
}

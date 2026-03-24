<?php

declare(strict_types=1);

namespace Modules\PageBuilder\Policies;

use App\Models\User;
use Modules\PageBuilder\Models\PageRevision;

final class PageRevisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canInOrganization('org.pages.manage');
    }

    public function view(User $user, PageRevision $revision): bool
    {
        return $user->canInOrganization('org.pages.manage', $revision->page?->organization);
    }

    public function create(User $user): bool
    {
        return $user->canInOrganization('org.pages.manage');
    }

    public function update(User $user, PageRevision $revision): bool
    {
        return $user->canInOrganization('org.pages.manage', $revision->page?->organization);
    }

    public function delete(User $user, PageRevision $revision): bool
    {
        return $user->canInOrganization('org.pages.manage', $revision->page?->organization);
    }
}

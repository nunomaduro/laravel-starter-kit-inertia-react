<?php

declare(strict_types=1);

namespace Modules\PageBuilder\Policies;

use App\Models\User;
use Modules\PageBuilder\Models\Page;

final class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canInOrganization('org.pages.manage');
    }

    public function view(User $user, Page $page): bool
    {
        if ($page->is_published) {
            return true;
        }

        return $user->canInOrganization('org.pages.manage', $page->organization);
    }

    public function create(User $user): bool
    {
        return $user->canInOrganization('org.pages.manage');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->canInOrganization('org.pages.manage', $page->organization);
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->canInOrganization('org.pages.manage', $page->organization);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Help\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Help\Models\HelpArticle;

final class HelpArticlePolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(?User $user, HelpArticle $article): bool
    {
        if ($article->is_published) {
            return true;
        }

        return $user !== null && $user->canInOrganization('org.help.manage', $article->organization);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.help.manage');
    }

    public function update(User $user, HelpArticle $article): bool
    {
        return $user->canInOrganization('org.help.manage', $article->organization);
    }

    public function delete(User $user, HelpArticle $article): bool
    {
        return $user->canInOrganization('org.help.manage', $article->organization);
    }

    public function restore(User $user, HelpArticle $article): bool
    {
        return $user->canInOrganization('org.help.manage', $article->organization);
    }

    public function forceDelete(User $user, HelpArticle $article): bool
    {
        return $user->canInOrganization('org.help.manage', $article->organization);
    }
}

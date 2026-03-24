<?php

declare(strict_types=1);

namespace Modules\Blog\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Blog\Models\Post;

final class PostPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(?User $user, Post $post): bool
    {
        if ($post->is_published) {
            return true;
        }

        return $user !== null && $user->belongsToOrganization($post->organization_id);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.blog.manage');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->canInOrganization('org.blog.manage', $post->organization);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->canInOrganization('org.blog.manage', $post->organization);
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->canInOrganization('org.blog.manage', $post->organization);
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->canInOrganization('org.blog.manage', $post->organization);
    }
}

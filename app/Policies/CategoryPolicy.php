<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Services\TenantContext;

final class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return TenantContext::check();
    }

    public function view(User $user, Category $category): bool
    {
        return $category->organization_id === TenantContext::id();
    }

    public function create(User $user): bool
    {
        if (! TenantContext::check()) {
            return false;
        }

        return $user->canInOrganization('org.manage', TenantContext::organization());
    }

    public function update(User $user, Category $category): bool
    {
        if ($category->organization_id !== TenantContext::id()) {
            return false;
        }

        return $user->canInOrganization('org.manage', TenantContext::organization());
    }

    public function delete(User $user, Category $category): bool
    {
        if ($category->organization_id !== TenantContext::id()) {
            return false;
        }

        return $user->canInOrganization('org.manage', TenantContext::organization());
    }

    public function restore(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $this->delete($user, $category);
    }
}

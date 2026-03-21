<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Features\ImpersonationFeature;
use App\Models\Organization;
use App\Support\FeatureHelper;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Admin-level capabilities: super-admin checks, org-admin checks, impersonation.
 *
 * @mixin \App\Models\User
 */
trait HasAdminCapabilities
{
    /**
     * Whether this user is admin or owner in at least one organization.
     * Uses a single query to avoid N+1 when checking from Filament.
     */
    public function isAdminInAnyOrganization(): bool
    {
        $orgIds = $this->organizations()->pluck('organizations.id')->all();
        if ($orgIds === []) {
            return false;
        }

        if (Organization::query()->whereIn('id', $orgIds)->where('owner_id', $this->id)->exists()) {
            return true;
        }

        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key');

        return DB::table($tableNames['model_has_roles'])
            ->join($tableNames['roles'], $tableNames['roles'].'.id', '=', $tableNames['model_has_roles'].'.role_id')
            ->where($tableNames['model_has_roles'].'.model_id', $this->id)
            ->where($tableNames['model_has_roles'].'.model_type', self::class)
            ->whereIn($tableNames['model_has_roles'].'.'.$teamKey, $orgIds)
            ->where($tableNames['roles'].'.name', 'admin')
            ->exists();
    }

    /**
     * Whether this user has the super-admin role (application-wide, global team).
     */
    public function isSuperAdmin(): bool
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key');

        return (bool) DB::table($tableNames['model_has_roles'])
            ->join($tableNames['roles'], $tableNames['roles'].'.id', '=', $tableNames['model_has_roles'].'.role_id')
            ->where($tableNames['model_has_roles'].'.model_id', $this->id)
            ->where($tableNames['model_has_roles'].'.model_type', self::class)
            ->where($tableNames['model_has_roles'].'.'.$teamKey, 0)
            ->where($tableNames['roles'].'.name', 'super-admin')
            ->exists();
    }

    public function isLastSuperAdmin(): bool
    {
        if (! $this->hasRole('super-admin')) {
            return false;
        }

        return Role::query()
            ->where('name', 'super-admin')
            ->withCount('users')
            ->first()
            ?->users_count === 1;
    }

    /**
     * Whether this user shares at least one organization with the given user.
     */
    public function sharesOrganizationWith(self $other): bool
    {
        $ourIds = $this->organizations()->pluck('organizations.id')->all();
        if ($ourIds === []) {
            return false;
        }

        return $other->organizations()->whereIn('organizations.id', $ourIds)->exists();
    }

    /**
     * Super-admin or org admin may impersonate when Impersonation feature is active.
     * Org admins may only impersonate team members (enforced in canBeImpersonated on target).
     */
    public function canImpersonate(): bool
    {
        if (! FeatureHelper::isActiveForClass(ImpersonationFeature::class, $this)) {
            return false;
        }

        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->isAdminInAnyOrganization();
    }

    /**
     * Super-admins cannot be impersonated.
     * Non–super-admins: super-admin can impersonate any; org admin only users in the same org(s).
     */
    public function canBeImpersonated(): bool
    {
        if ($this->hasRole('super-admin')) {
            return false;
        }

        $impersonator = auth()->user();
        if (! $impersonator instanceof self) {
            return false;
        }

        if ($impersonator->hasRole('super-admin')) {
            return true;
        }

        if ($impersonator->isAdminInAnyOrganization()) {
            return $this->sharesOrganizationWith($impersonator);
        }

        return false;
    }
}

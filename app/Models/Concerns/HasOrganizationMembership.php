<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Organization membership: relationships, switching, and membership checks.
 *
 * @mixin \App\Models\User
 */
trait HasOrganizationMembership
{
    /**
     * Organizations this user belongs to.
     *
     * @return BelongsToMany<Organization, $this>
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot(['is_default', 'joined_at', 'invited_by'])
            ->withTimestamps();
    }

    /**
     * Organizations this user owns.
     *
     * @return HasMany<Organization, $this>
     */
    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    /**
     * The user's default organization (is_default = true on pivot).
     */
    public function defaultOrganization(): ?Organization
    {
        return $this->organizations()->wherePivot('is_default', true)->first();
    }

    /**
     * Switch the current tenant context to the given organization.
     * Validates the user is a member. Use for web (session) or API (stateless for request).
     */
    public function switchOrganization(Organization|int $organization): bool
    {
        $org = $organization instanceof Organization
            ? $organization
            : Organization::query()->find($organization);

        if (! $org instanceof Organization || ! $this->belongsToOrganization($org->id)) {
            return false;
        }

        TenantContext::set($org);

        return true;
    }

    /**
     * Whether the user belongs to the given organization (by ID).
     */
    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organizations()->where('organizations.id', $organizationId)->exists();
    }
}

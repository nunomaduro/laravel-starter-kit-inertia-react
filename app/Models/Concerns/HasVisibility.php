<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\VisibilityEnum;
use App\Models\Organization;
use App\Models\Scopes\VisibilityScope;
use App\Models\Shareable;
use App\Models\User;
use App\Services\TenantContext;
use DateTimeInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

/**
 * Trait for models that support visibility and sharing.
 *
 * Combines organization scoping with visibility levels:
 * - Global: Visible to all organizations (read-only; super-admin only)
 * - Organization: Only visible to members of the owning organization
 * - Shared: Visible to owner org + explicitly shared targets
 *
 * Models using this trait MUST have:
 * - organization_id column (nullable for global items)
 * - visibility column (string, defaults to 'organization')
 * - Optional: cloned_from column for copy-on-write tracking
 *
 * Do NOT use BelongsToOrganization on the same model; this trait applies VisibilityScope.
 *
 * @property int|null $organization_id
 * @property VisibilityEnum $visibility
 * @property int|null $cloned_from
 * @property-read Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Shareable> $shares
 */
trait HasVisibility
{
    public function initializeHasVisibility(): void
    {
        if (property_exists($this, 'guarded') && is_array($this->guarded) && $this->guarded !== ['*']) {
            if (! in_array('visibility', $this->guarded, true)) {
                $this->guarded[] = 'visibility';
            }

            if (! in_array('organization_id', $this->guarded, true)) {
                $this->guarded[] = 'organization_id';
            }
        }

        $this->casts['visibility'] = VisibilityEnum::class;
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function belongsToOrganization(Organization|int $organization): bool
    {
        $organizationId = $organization instanceof Organization
            ? $organization->id
            : $organization;

        return $this->organization_id === $organizationId;
    }

    public function belongsToCurrentOrganization(): bool
    {
        return $this->organization_id === TenantContext::id();
    }

    /**
     * @return MorphMany<Shareable, $this>
     */
    public function shares(): MorphMany
    {
        return $this->morphMany(Shareable::class, 'shareable');
    }

    /**
     * @return Collection<int, Organization>
     */
    public function sharedWithOrganizations(): Collection
    {
        return Organization::query()->whereIn('id', $this->shares()
            ->where('target_type', Organization::class)
            ->active()
            ->pluck('target_id'))->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function sharedWithUsers(): Collection
    {
        return User::query()->whereIn('id', $this->shares()
            ->where('target_type', User::class)
            ->active()
            ->pluck('target_id'))->get();
    }

    public function isGlobal(): bool
    {
        return $this->visibility === VisibilityEnum::Global;
    }

    public function isShared(): bool
    {
        return $this->visibility === VisibilityEnum::Shared;
    }

    public function isOrgOnly(): bool
    {
        return $this->visibility === VisibilityEnum::Organization;
    }

    public function canBeViewedBy(User $user): bool
    {
        if ($this->isGlobal()) {
            return true;
        }

        if ($this->organization_id && $user->belongsToOrganization($this->organization_id)) {
            return true;
        }

        $currentOrg = TenantContext::get();
        if ($currentOrg && $this->isSharedWithOrganization($currentOrg->id)) {
            return true;
        }

        return $this->isSharedWithUser($user->id);
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->isGlobal()) {
            return false;
        }

        if ($this->organization_id) {
            $organization = $this->organization;
            if ($organization && $organization->hasAdmin($user)) {
                return true;
            }
        }

        $currentOrg = TenantContext::get();
        if ($currentOrg && $this->hasEditPermissionForOrganization($currentOrg->id)) {
            return true;
        }

        return $this->hasEditPermissionForUser($user->id);
    }

    public function isSharedWithOrganization(int $organizationId): bool
    {
        return $this->shares()
            ->where('target_type', Organization::class)
            ->where('target_id', $organizationId)
            ->active()
            ->exists();
    }

    public function isSharedWithUser(int $userId): bool
    {
        return $this->shares()
            ->where('target_type', User::class)
            ->where('target_id', $userId)
            ->active()
            ->exists();
    }

    public function hasEditPermissionForOrganization(int $organizationId): bool
    {
        return $this->shares()
            ->where('target_type', Organization::class)
            ->where('target_id', $organizationId)
            ->withEditPermission()
            ->active()
            ->exists();
    }

    public function hasEditPermissionForUser(int $userId): bool
    {
        return $this->shares()
            ->where('target_type', User::class)
            ->where('target_id', $userId)
            ->withEditPermission()
            ->active()
            ->exists();
    }

    /**
     * @throws InvalidArgumentException
     * @throws AuthorizationException
     */
    public function shareWithOrganization(Organization|int $organization, string $permission = 'view', ?DateTimeInterface $expiresAt = null): Shareable
    {
        if (! Shareable::isValidPermission($permission)) {
            throw new InvalidArgumentException(
                sprintf('Invalid permission "%s". Valid permissions are: %s', $permission, implode(', ', Shareable::VALID_PERMISSIONS))
            );
        }

        $organizationId = $organization instanceof Organization ? $organization->id : $organization;
        $targetOrg = $organization instanceof Organization ? $organization : Organization::query()->findOrFail($organizationId);

        $authUser = auth()->user();
        if ($authUser instanceof User) {
            Gate::authorize('shareItem', [Shareable::class, $this, $targetOrg, $permission]);
        }

        return DB::transaction(function () use ($organizationId, $permission, $expiresAt): Shareable {
            $share = $this->shares()->updateOrCreate(
                [
                    'target_type' => Organization::class,
                    'target_id' => $organizationId,
                ],
                [
                    'permission' => $permission,
                    'shared_by' => auth()->id(),
                    'expires_at' => $expiresAt,
                ]
            );

            if ($this->visibility !== VisibilityEnum::Global) {
                $this->visibility = VisibilityEnum::Shared;
                $this->save();
            }

            return $share;
        });
    }

    /**
     * @throws InvalidArgumentException
     * @throws AuthorizationException
     */
    public function shareWithUser(User|int $user, string $permission = 'view', ?DateTimeInterface $expiresAt = null): Shareable
    {
        if (! Shareable::isValidPermission($permission)) {
            throw new InvalidArgumentException(
                sprintf('Invalid permission "%s". Valid permissions are: %s', $permission, implode(', ', Shareable::VALID_PERMISSIONS))
            );
        }

        $userId = $user instanceof User ? $user->id : $user;
        $targetUser = $user instanceof User ? $user : User::query()->findOrFail($userId);

        $authUser = auth()->user();
        if ($authUser instanceof User) {
            Gate::authorize('shareItem', [Shareable::class, $this, $targetUser, $permission]);
        }

        return DB::transaction(function () use ($userId, $permission, $expiresAt): Shareable {
            $share = $this->shares()->updateOrCreate(
                [
                    'target_type' => User::class,
                    'target_id' => $userId,
                ],
                [
                    'permission' => $permission,
                    'shared_by' => auth()->id(),
                    'expires_at' => $expiresAt,
                ]
            );

            if ($this->visibility !== VisibilityEnum::Global) {
                $this->visibility = VisibilityEnum::Shared;
                $this->save();
            }

            return $share;
        });
    }

    public function revokeOrganizationShare(Organization|int $organization): bool
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        return DB::transaction(function () use ($organizationId): bool {
            $deleted = $this->shares()
                ->where('target_type', Organization::class)
                ->where('target_id', $organizationId)
                ->delete();

            if ($this->visibility === VisibilityEnum::Shared && ! $this->shares()->exists()) {
                $this->visibility = VisibilityEnum::Organization;
                $this->save();
            }

            return $deleted > 0;
        });
    }

    public function revokeUserShare(User|int $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return DB::transaction(function () use ($userId): bool {
            $deleted = $this->shares()
                ->where('target_type', User::class)
                ->where('target_id', $userId)
                ->delete();

            if ($this->visibility === VisibilityEnum::Shared && ! $this->shares()->exists()) {
                $this->visibility = VisibilityEnum::Organization;
                $this->save();
            }

            return $deleted > 0;
        });
    }

    /**
     * Clone this item for a specific organization (copy-on-write).
     */
    public function cloneForOrganization(Organization|int $organization): static
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        $clone = $this->replicate();
        $clone->organization_id = $organizationId;
        $clone->visibility = VisibilityEnum::Organization;

        if (in_array('cloned_from', $this->getFillable(), true) || $this->isFillable('cloned_from')) {
            $clone->cloned_from = $this->id;
        }

        $clone->save();

        return $clone;
    }

    protected static function bootHasVisibility(): void
    {
        static::addGlobalScope(new VisibilityScope);

        static::creating(function ($model): void {
            if (! $model->visibility) {
                $model->visibility = $model->organization_id
                    ? VisibilityEnum::Organization
                    : VisibilityEnum::Global;
            }

            if ($model->visibility !== VisibilityEnum::Global && ! $model->organization_id) {
                $model->organization_id = TenantContext::id();
            }

            if ($model->visibility === VisibilityEnum::Global && ! auth()->user()?->isSuperAdmin()) {
                $model->visibility = VisibilityEnum::Organization;
                $model->organization_id = TenantContext::id();
            }
        });

        static::updating(function ($model): void {
            if ($model->isDirty('organization_id') && ! auth()->user()?->isSuperAdmin()) {
                $model->organization_id = $model->getOriginal('organization_id');
            }

            if ($model->isDirty('visibility')
                && $model->visibility === VisibilityEnum::Global
                && ! auth()->user()?->isSuperAdmin()) {
                $model->visibility = $model->getOriginal('visibility');
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeForOrganization($query, Organization|int $organization)
    {
        $organizationId = $organization instanceof Organization
            ? $organization->id
            : $organization;

        return $query->where($this->getTable().'.organization_id', $organizationId);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeAccessibleBy($query, User $user)
    {
        return $query;
    }
}

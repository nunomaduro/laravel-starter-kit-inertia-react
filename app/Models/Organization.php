<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\TenantContext;
use App\Traits\Billing\HasBilling;
use App\Traits\Billing\HasCredits;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;
use Mattiverse\Userstamps\Traits\Userstamps;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use function getPermissionsTeamId;
use function setPermissionsTeamId;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property array<string, mixed>|null $settings
 * @property int|null $owner_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read User|null $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $members
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrganizationInvitation> $invitations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrganizationInvitation> $pendingInvitations
 */
final class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasBilling;

    use HasCredits;
    use HasFactory;
    use HasPlanSubscriptions;
    use HasSlug;
    use LogsActivity;
    use SoftDeletes;
    use Userstamps;

    /**
     * Role names that can be assigned when adding/inviting members (owner is set via transfer only).
     *
     * @var list<string>
     */
    public const array ASSIGNABLE_ORG_ROLES = ['admin', 'member'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'settings',
        'owner_id',
        'billing_email',
        'tax_id',
        'billing_address',
        'stripe_customer_id',
        'paddle_customer_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['is_default', 'joined_at', 'invited_by'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->users();
    }

    /**
     * @return HasMany<OrganizationDomain, $this>
     */
    public function domains(): HasMany
    {
        return $this->hasMany(OrganizationDomain::class);
    }

    /**
     * @return HasMany<OrganizationInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    /**
     * @return HasMany<OrganizationInvitation, $this>
     */
    public function pendingInvitations(): HasMany
    {
        return $this->invitations()
            ->where('status', OrganizationInvitation::STATUS_PENDING)
            ->where('expires_at', '>', now());
    }

    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Whether the user has admin role in this organization (or is owner).
     */
    public function hasAdmin(User $user): bool
    {
        if ($this->isOwner($user)) {
            return true;
        }

        return in_array('admin', $user->roleNamesInOrganization($this), true);
    }

    /**
     * Get the user's primary role in this organization (owner, admin, or member).
     */
    public function getUserRole(User $user): ?string
    {
        if ($this->isOwner($user)) {
            return 'owner';
        }

        $roles = $user->roleNamesInOrganization($this);

        return $roles[0] ?? null;
    }

    /**
     * Add a user to the organization with the given role (admin or member).
     * Does not check authorization; use from actions/controllers that enforce policy.
     * Creates org-scoped roles if they do not exist yet.
     */
    public function addMember(User $user, string $role, ?User $invitedBy = null): void
    {
        $isStandardRole = in_array($role, self::ASSIGNABLE_ORG_ROLES, true);
        $isCustomRole = str_starts_with($role, 'custom_');

        if (! $isStandardRole && ! $isCustomRole) {
            throw new InvalidArgumentException(sprintf("Invalid role '%s'. Must be one of: ", $role).implode(', ', self::ASSIGNABLE_ORG_ROLES).' — or a custom role.');
        }

        $this->ensureOrgRolesExist();

        $this->users()->syncWithoutDetaching([
            $user->id => [
                'is_default' => false,
                'joined_at' => now(),
                'invited_by' => $invitedBy?->id,
            ],
        ]);

        $previousContext = TenantContext::get();
        TenantContext::set($this);
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($this->id);
        try {
            $teamKey = config('permission.column_names.team_foreign_key');
            $guard = 'web';
            $roleModel = Role::query()
                ->where('name', $role)
                ->where('guard_name', $guard)
                ->where($teamKey, $this->id)
                ->first();
            if ($roleModel instanceof Role) {
                // Insert by role id so model_has_roles always receives bigint (Spatie attach can mis-bind on PostgreSQL).
                $tableNames = config('permission.table_names');
                $pivotRole = config('permission.column_names.role_pivot_key') ?? 'role_id';
                $modelMorphKey = config('permission.column_names.model_morph_key') ?? 'model_id';
                DB::table($tableNames['model_has_roles'])->insertOrIgnore([
                    $pivotRole => $roleModel->getKey(),
                    'model_type' => User::class,
                    $modelMorphKey => $user->getKey(),
                    $teamKey => $this->id,
                ]);
                $user->unsetRelation('roles');
            }
        } finally {
            setPermissionsTeamId($previousTeamId);
            if ($previousContext instanceof self) {
                TenantContext::set($previousContext);
            } else {
                TenantContext::forget();
            }
        }
    }

    /**
     * Remove a user from the organization. Revokes org-scoped roles.
     */
    public function removeMember(User $user): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key');
        DB::table($tableNames['model_has_roles'])
            ->where('model_id', $user->id)
            ->where('model_type', User::class)
            ->where($teamKey, $this->id)
            ->delete();
        $this->users()->detach($user->id);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logAll()
            ->logExcept(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'embedding', 'api_token']);
    }

    protected function casts(): array
    {
        return [
            'billing_address' => 'array',
        ];
    }

    /**
     * Ensure admin and member roles exist for this organization (idempotent).
     */
    private function ensureOrgRolesExist(): void
    {
        $teamKey = config('permission.column_names.team_foreign_key');
        $guard = 'web';

        foreach (self::ASSIGNABLE_ORG_ROLES as $roleName) {
            $exists = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', $guard)
                ->where($teamKey, $this->id)
                ->exists();

            if (! $exists) {
                Role::query()->create([
                    'name' => $roleName,
                    'guard_name' => $guard,
                    $teamKey => $this->id,
                ]);
            }
        }

        resolve(\App\Services\Organization\OrganizationRoleService::class)->syncRolePermissions($this);
    }
}

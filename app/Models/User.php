<?php

declare(strict_types=1);

namespace App\Models;

use App\Features\ImpersonationFeature;
use App\Models\Concerns\Categorizable;
use App\Models\Concerns\HasOrganizationPermissions;
use App\Services\TenantContext;
use App\Support\FeatureHelper;
use App\Traits\Billing\HasAffiliate;
use BeyondCode\Vouchers\Traits\CanRedeemVouchers;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jijunair\LaravelReferral\Traits\Referrable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Scout\Searchable;
use LevelUp\Experience\Concerns\GiveExperience;
use LevelUp\Experience\Concerns\HasAchievements;
use Override;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Spatie\Tags\HasTags;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string|null $avatar
 * @property-read string|null $avatar_profile
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read string|null $two_factor_secret
 * @property-read string|null $two_factor_recovery_codes
 * @property-read CarbonInterface|null $two_factor_confirmed_at
 * @property bool $onboarding_completed
 * @property array<string>|null $onboarding_steps_completed
 * @property string $theme_mode
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 */
final class User extends Authenticatable implements ExportsPersonalData, FilamentUser, HasMedia, MustVerifyEmail
{
    use CanRedeemVouchers;
    use Categorizable;
    use GiveExperience;
    use HasAchievements;
    use HasAffiliate;
    use HasApiTokens;
    use HasFactory;
    use HasOrganizationPermissions;
    use HasRoles;
    use HasTags;
    use InteractsWithMedia;
    use LogsActivity;
    use Notifiable;
    use Referrable;
    use Searchable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * @var list<string>
     */
    #[Override]
    protected $appends = [
        'avatar',
        'avatar_profile',
    ];

    /**
     * @var list<string>
     */
    #[Override]
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the indexable data array for the model (Typesense).
     * Only safe, searchable fields; id and created_at must be string and int64.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->timestamp,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('avatar')
            ->fit(Fit::Crop, 48, 48)
            ->nonQueued();

        $this->addMediaConversion('profile')
            ->performOnCollections('avatar')
            ->fit(Fit::Crop, 192, 192)
            ->nonQueued();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logOnly(['name', 'email', 'email_verified_at', 'two_factor_confirmed_at']);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->can('access admin panel');
    }

    /**
     * Create a personal access token with abilities derived from the user's permissions.
     * Users with bypass-permissions get ['*']; others get their permission names.
     */
    public function createTokenWithPermissionAbilities(string $name, ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        $abilities = $this->can('bypass-permissions')
            ? ['*']
            : $this->getAllPermissions()->pluck('name')->all();

        return $this->createToken($name, $abilities, $expiresAt);
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
     * Select the personal data to be exported for GDPR compliance.
     */
    public function selectPersonalData(PersonalDataSelection $personalDataSelection): void
    {
        $personalDataSelection->add('user.json', [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ]);
    }

    public function personalDataExportName(): string
    {
        return 'personal-data-'.Str::slug($this->name).'.zip';
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
     * @return HasMany<UserTermsAcceptance, $this>
     */
    public function termsAcceptances(): HasMany
    {
        return $this->hasMany(UserTermsAcceptance::class);
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'onboarding_completed' => 'boolean',
            'onboarding_steps_completed' => 'array',
            'theme_mode' => 'string',
        ];
    }

    /**
     * Avatar URL (thumb conversion) for nav/header, or null when no avatar.
     */
    protected function avatar(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function () {
            $url = $this->getFirstMediaUrl('avatar', 'thumb');

            return $url !== '' ? $url : null;
        });
    }

    /**
     * Avatar URL (profile conversion) for profile/settings preview, or null when no avatar.
     */
    protected function avatarProfile(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function () {
            $url = $this->getFirstMediaUrl('avatar', 'profile');

            return $url !== '' ? $url : null;
        });
    }
}

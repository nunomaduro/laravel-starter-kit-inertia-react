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
use Database\Factories\UserFactory;
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
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 */
final class User extends Authenticatable implements ExportsPersonalData, FilamentUser, HasMedia, MustVerifyEmail
{
    /**
     * @use HasFactory<UserFactory>
     */
    use CanRedeemVouchers, Categorizable, GiveExperience, HasAchievements, HasAffiliate, HasApiTokens, HasFactory, HasOrganizationPermissions, HasRoles, HasTags, InteractsWithMedia, LogsActivity, Notifiable, Referrable, Searchable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * @var list<string>
     */
    protected $appends = [
        'avatar',
        'avatar_profile',
    ];

    /**
     * @var list<string>
     */
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
     * Only super-admins may impersonate, and only when Impersonation feature is active.
     */
    public function canImpersonate(): bool
    {
        return $this->hasRole('super-admin')
            && FeatureHelper::isActiveForClass(ImpersonationFeature::class, $this);
    }

    /**
     * Super-admins cannot be impersonated.
     */
    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole('super-admin');
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
        ];
    }

    /**
     * Avatar URL (thumb conversion) for nav/header, or null when no avatar.
     */
    protected function getAvatarAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('avatar', 'thumb');

        return $url !== '' ? $url : null;
    }

    /**
     * Avatar URL (profile conversion) for profile/settings preview, or null when no avatar.
     */
    protected function getAvatarProfileAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('avatar', 'profile');

        return $url !== '' ? $url : null;
    }
}

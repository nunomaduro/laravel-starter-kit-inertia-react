<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\Categorizable;
use App\Models\Concerns\HasAdminCapabilities;
use App\Models\Concerns\HasMediaProfile;
use App\Models\Concerns\HasOrganizationMembership;
use App\Models\Concerns\HasOrganizationPermissions;
use App\Traits\Billing\HasAffiliate;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use BeyondCode\Vouchers\Traits\CanRedeemVouchers;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Deligoez\LaravelModelHashId\Traits\HasHashId;
use Deligoez\LaravelModelHashId\Traits\HasHashIdRouting;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Onboard\Concerns\GetsOnboarded;
use Spatie\Onboard\Concerns\Onboardable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Spatie\Tags\HasTags;
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;

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
 * @property string $theme_mode
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 * @property-read string $hashId
 * @property string|null $phone
 */
final class User extends Authenticatable implements ExportsPersonalData, FilamentUser, HasMedia, MustVerifyEmail, Onboardable
{
    use CanRedeemVouchers;
    use Categorizable;
    use GetsOnboarded;
    use GiveExperience;
    use HasAchievements;
    use HasAdminCapabilities;
    use HasAffiliate;
    use HasApiTokens;
    use HasFactory;
    use HasHashId;
    use HasHashIdRouting;
    use HasMediaProfile;
    use HasOrganizationMembership;
    use HasOrganizationPermissions;
    use HasRoles;
    use HasTags;
    use InteractsWithMedia {
        HasMediaProfile::registerMediaCollections insteadof InteractsWithMedia;
        HasMediaProfile::registerMediaConversions insteadof InteractsWithMedia;
    }
    use LogsActivity;
    use Notifiable;
    use Referrable;
    use Searchable;
    use SnoozeNotifiable;
    use SoftCascadeTrait;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * @var list<string>
     */
    protected $softCascade = [
        'ownedOrganizations',
        'socialAccounts',
        'termsAcceptances',
        'notificationPreferences',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'onboarding_completed',
        'theme_mode',
        'phone',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'avatar',
        'avatar_profile',
        'hash_id',
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
     * @return HasMany<SocialAccount, $this>
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * @return HasMany<UserTermsAcceptance, $this>
     */
    public function termsAcceptances(): HasMany
    {
        return $this->hasMany(UserTermsAcceptance::class);
    }

    /**
     * @return HasMany<NotificationPreference, $this>
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function prefersChannel(string $notificationType, string $channel): bool
    {
        $pref = $this->notificationPreferences()
            ->where('notification_type', $notificationType)
            ->first();

        if (! $pref) {
            return true; // Default: all channels enabled
        }

        return match ($channel) {
            'database' => $pref->via_database,
            'email' => $pref->via_email,
            default => false,
        };
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
            'theme_mode' => 'string',
            'tags' => 'array',
            'position' => 'integer',
        ];
    }
}

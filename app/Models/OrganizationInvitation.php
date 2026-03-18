<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrganizationInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Throwable;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $email
 * @property string $role
 * @property string $status
 * @property string $token
 * @property int $invited_by
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon|null $accepted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Organization $organization
 * @property-read User $inviter
 */
final class OrganizationInvitation extends Model
{
    /** @use HasFactory<OrganizationInvitationFactory> */
    use HasFactory;

    use LogsActivity;

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_ACCEPTED = 'accepted';

    public const string STATUS_CANCELLED = 'cancelled';

    public const string STATUS_EXPIRED = 'expired';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'email',
        'role',
        'invited_by',
        'expires_at',
    ];

    /**
     * @var list<string>
     */
    protected $guarded = [
        'token',
        'status',
        'accepted_at',
    ];

    public static function findByToken(string $token): ?self
    {
        return self::query()->where('token', $token)->first();
    }

    public static function findValidByToken(string $token): ?self
    {
        return self::query()
            ->where('token', $token)
            ->where('status', self::STATUS_PENDING)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->isPending() && ! $this->isExpired();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function markAsAccepted(): self
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->accepted_at = now();
        $this->save();

        return $this;
    }

    /**
     * Accept the invitation for a user. Adds user to organization with invited role and marks invitation accepted.
     *
     * @throws InvalidArgumentException If role is invalid
     * @throws Throwable If transaction fails
     */
    public function acceptForUser(User $user): self
    {
        $this->validateRole();

        return DB::transaction(function () use ($user): self {
            $this->organization->addMember($user, $this->role, $this->inviter);
            $this->status = self::STATUS_ACCEPTED;
            $this->accepted_at = now();
            $this->save();

            return $this;
        });
    }

    public function markAsCancelled(): self
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();

        return $this;
    }

    public function resend(): self
    {
        if (! $this->isPending()) {
            return $this;
        }

        $this->token = Str::random(64);
        $days = (int) config('tenancy.invitations.expires_in_days', 7);
        $this->expires_at = now()->addDays($days);
        $this->save();

        return $this;
    }

    public function canBeResent(): bool
    {
        return $this->isPending();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logAll()
            ->logExcept(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'embedding', 'api_token']);
    }

    protected static function booted(): void
    {
        self::creating(function (OrganizationInvitation $invitation): void {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }

            if (empty($invitation->status)) {
                $invitation->status = self::STATUS_PENDING;
            }

            if (empty($invitation->expires_at)) {
                $days = (int) config('tenancy.invitations.expires_in_days', 7);
                $invitation->expires_at = now()->addDays($days);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @throws InvalidArgumentException If role is not a valid org role
     */
    private function validateRole(): void
    {
        if (in_array($this->role, Organization::ASSIGNABLE_ORG_ROLES, true)) {
            return;
        }

        // Custom roles (created by org admins) are also valid
        if (str_starts_with($this->role, 'custom_')) {
            return;
        }

        throw new InvalidArgumentException(sprintf("Invalid role '%s'. Must be one of: ", $this->role).implode(', ', Organization::ASSIGNABLE_ORG_ROLES).' — or a custom role.');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $domain
 * @property string $type
 * @property string $status
 * @property bool $is_verified
 * @property string|null $verification_token
 * @property bool $is_primary
 * @property string|null $cname_target
 * @property string|null $failure_reason
 * @property int $dns_check_attempts
 * @property \Carbon\Carbon|null $verified_at
 * @property \Carbon\Carbon|null $last_dns_check_at
 * @property \Carbon\Carbon|null $ssl_issued_at
 * @property \Carbon\Carbon|null $ssl_expires_at
 * @property-read Organization $organization
 */
final class OrganizationDomain extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use LogsActivity;

    protected $fillable = [
        'organization_id',
        'domain',
        'type',
        'status',
        'is_verified',
        'verification_token',
        'is_primary',
        'verified_at',
        'cname_target',
        'failure_reason',
        'dns_check_attempts',
        'last_dns_check_at',
        'ssl_issued_at',
        'ssl_expires_at',
    ];

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPendingDns(): bool
    {
        return $this->status === 'pending_dns';
    }

    public function hasExpiredSsl(): bool
    {
        return $this->ssl_expires_at !== null && $this->ssl_expires_at->isPast();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logAll()
            ->logExcept(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'embedding', 'api_token']);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
            'last_dns_check_at' => 'datetime',
            'ssl_issued_at' => 'datetime',
            'ssl_expires_at' => 'datetime',
            'dns_check_attempts' => 'integer',
        ];
    }
}

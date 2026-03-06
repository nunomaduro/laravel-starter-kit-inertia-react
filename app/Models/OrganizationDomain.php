<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $domain
 * @property string $type
 * @property bool $is_verified
 * @property string|null $verification_token
 * @property bool $is_primary
 * @property \Carbon\Carbon|null $verified_at
 * @property-read Organization $organization
 */
final class OrganizationDomain extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use LogsActivity;

    #[Override]
    protected $fillable = [
        'organization_id',
        'domain',
        'type',
        'is_verified',
        'verification_token',
        'is_primary',
        'verified_at',
    ];

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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
        ];
    }
}

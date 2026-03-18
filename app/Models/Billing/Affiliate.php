<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Akaunting\Money\Money;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

final class Affiliate extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_ACTIVE = 'active';

    public const string STATUS_SUSPENDED = 'suspended';

    public const string STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'affiliate_code',
        'status',
        'commission_rate',
        'payment_email',
        'payment_method',
        'payment_details',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'total_referrals',
        'successful_conversions',
        'admin_notes',
        'approved_at',
    ];

    public static function generateUniqueCode(): string
    {
        do {
            $code = mb_strtoupper(Str::random(8));
        } while (self::query()->where('affiliate_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function approve(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);
    }

    public function suspend(): void
    {
        $this->update(['status' => self::STATUS_SUSPENDED]);
    }

    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
    }

    public function getFormattedTotalEarnings(): string
    {
        return new Money($this->total_earnings, config('billing.currency', 'USD'))->format();
    }

    public function getFormattedPendingEarnings(): string
    {
        return new Money($this->pending_earnings, config('billing.currency', 'USD'))->format();
    }

    public function getConversionRate(): float
    {
        if ($this->total_referrals === 0) {
            return 0.0;
        }

        return ($this->successful_conversions / $this->total_referrals) * 100;
    }

    protected static function booted(): void
    {
        self::creating(function (self $affiliate): void {
            if (empty($affiliate->affiliate_code)) {
                $affiliate->affiliate_code = self::generateUniqueCode();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'payment_details' => 'array',
            'total_earnings' => 'integer',
            'pending_earnings' => 'integer',
            'paid_earnings' => 'integer',
            'total_referrals' => 'integer',
            'successful_conversions' => 'integer',
            'approved_at' => 'datetime',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AffiliateCommission extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_APPROVED = 'approved';

    public const string STATUS_PAID = 'paid';

    public const string STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'affiliate_id',
        'referred_organization_id',
        'invoice_id',
        'amount',
        'currency',
        'status',
        'description',
        'approved_at',
        'paid_at',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referredOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'referred_organization_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getFormattedAmount(): string
    {
        return new Money($this->amount, new Currency($this->currency))->format();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function approve(): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
        $this->affiliate->increment('pending_earnings', $this->amount);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
        $this->affiliate->decrement('pending_earnings', $this->amount);
        $this->affiliate->increment('paid_earnings', $this->amount);
    }

    public function cancel(): void
    {
        if ($this->status === self::STATUS_APPROVED) {
            $this->affiliate->decrement('pending_earnings', $this->amount);
        }

        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}

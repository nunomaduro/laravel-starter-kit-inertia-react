<?php

declare(strict_types=1);

namespace Modules\Billing\Models;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Billing\States\AffiliateCommission\Approved;
use Modules\Billing\States\AffiliateCommission\Cancelled;
use Modules\Billing\States\AffiliateCommission\CommissionStatus;
use Modules\Billing\States\AffiliateCommission\Paid;
use Modules\Billing\States\AffiliateCommission\Pending;
use Spatie\ModelStates\HasStates;

final class AffiliateCommission extends Model
{
    use HasStates;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

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
        return $this->status->equals(Pending::class);
    }

    public function approve(): void
    {
        $this->status->transitionTo(Approved::class);
    }

    public function markAsPaid(): void
    {
        $this->status->transitionTo(Paid::class);
    }

    public function cancel(): void
    {
        $this->status->transitionTo(Cancelled::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => CommissionStatus::class,
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}

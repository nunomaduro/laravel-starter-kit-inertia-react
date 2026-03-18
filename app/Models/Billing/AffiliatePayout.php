<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Models\User;
use App\States\AffiliatePayout\Completed;
use App\States\AffiliatePayout\Failed;
use App\States\AffiliatePayout\PayoutStatus;
use App\States\AffiliatePayout\Pending;
use App\States\AffiliatePayout\Processing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property int $affiliate_id
 * @property int $amount
 * @property string $currency
 * @property PayoutStatus $status
 * @property string $payment_method
 * @property string|null $transaction_id
 * @property string|null $notes
 * @property int|null $processed_by
 * @property \Carbon\Carbon|null $processed_at
 * @property-read Affiliate $affiliate
 * @property-read User|null $processor
 */
final class AffiliatePayout extends Model
{
    use HasStates;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'affiliate_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'notes',
        'processed_by',
        'processed_at',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getFormattedAmount(): string
    {
        return new Money($this->amount, new Currency($this->currency))->format();
    }

    public function isPending(): bool
    {
        return $this->status->equals(Pending::class);
    }

    public function isCompleted(): bool
    {
        return $this->status->equals(Completed::class);
    }

    public function markAsProcessing(): void
    {
        $this->status->transitionTo(Processing::class);
    }

    public function markAsCompleted(string $transactionId, User $processor): void
    {
        $this->status->transitionTo(Completed::class, $transactionId, $processor);
    }

    public function markAsFailed(string $notes, User $processor): void
    {
        $this->status->transitionTo(Failed::class, $notes, $processor);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => PayoutStatus::class,
            'processed_at' => 'datetime',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $affiliate_id
 * @property int $amount
 * @property string $currency
 * @property string $status
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
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_PROCESSING = 'processing';

    public const string STATUS_COMPLETED = 'completed';

    public const string STATUS_FAILED = 'failed';

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
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsCompleted(string $transactionId, User $processor): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'transaction_id' => $transactionId,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $notes, User $processor): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $notes,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ]);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'processed_at' => 'datetime',
        ];
    }
}

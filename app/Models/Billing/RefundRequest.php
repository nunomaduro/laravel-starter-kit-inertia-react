<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Enums\Billing\RefundStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $organization_id
 * @property int $invoice_id
 * @property int $amount
 * @property string|null $reason
 * @property RefundStatus $status
 * @property \Carbon\Carbon|null $processed_at
 * @property int|null $processed_by
 */
final class RefundRequest extends Model
{
    use BelongsToOrganization;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'invoice_id',
        'amount',
        'reason',
        'status',
        'processed_at',
        'processed_by',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => RefundStatus::class,
            'processed_at' => 'datetime',
        ];
    }
}

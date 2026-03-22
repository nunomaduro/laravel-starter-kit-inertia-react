<?php

declare(strict_types=1);

namespace Modules\Billing\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Billing\Database\Factories\RefundRequestFactory;
use Modules\Billing\States\RefundRequest\RefundRequestStatus;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property int $organization_id
 * @property int $invoice_id
 * @property int $amount
 * @property string|null $reason
 * @property RefundRequestStatus $status
 * @property \Carbon\Carbon|null $processed_at
 * @property int|null $processed_by
 */
final class RefundRequest extends Model
{
    use BelongsToOrganization;
    use HasStates;
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

    protected static function newFactory(): RefundRequestFactory
    {
        return RefundRequestFactory::new();
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => RefundRequestStatus::class,
            'processed_at' => 'datetime',
        ];
    }
}

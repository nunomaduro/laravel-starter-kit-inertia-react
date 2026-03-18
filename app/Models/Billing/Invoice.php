<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Models\Concerns\BelongsToOrganization;
use Deligoez\LaravelModelHashId\Traits\HasHashId;
use Deligoez\LaravelModelHashId\Traits\HasHashIdRouting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $billable_type
 * @property int $billable_id
 * @property string $number
 * @property string $status
 * @property string $currency
 * @property int $subtotal
 * @property int $tax
 * @property int $total
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon|null $due_date
 * @property array|null $line_items
 * @property array|null $billing_address
 * @property int|null $payment_gateway_id
 * @property string|null $gateway_invoice_id
 * @property-read string $hashId
 */
final class Invoice extends Model
{
    use BelongsToOrganization;
    use HasHashId;
    use HasHashIdRouting;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * @var list<string>
     */
    protected $appends = [
        'hash_id',
    ];

    protected $fillable = [
        'billable_type',
        'billable_id',
        'number',
        'status',
        'currency',
        'subtotal',
        'tax',
        'total',
        'paid_at',
        'due_date',
        'line_items',
        'billing_address',
        'payment_gateway_id',
        'gateway_invoice_id',
    ];

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'tax' => 'integer',
            'total' => 'integer',
            'paid_at' => 'datetime',
            'due_date' => 'date',
            'line_items' => 'array',
            'billing_address' => 'array',
        ];
    }
}

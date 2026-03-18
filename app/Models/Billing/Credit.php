<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Enums\Billing\CreditTransactionType;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $creditable_type
 * @property int $creditable_id
 * @property int $amount
 * @property int $running_balance
 * @property CreditTransactionType $type
 * @property string|null $description
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
final class Credit extends Model
{
    use BelongsToOrganization;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'creditable_type',
        'creditable_id',
        'amount',
        'running_balance',
        'type',
        'description',
        'metadata',
        'expires_at',
    ];

    public function creditable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'running_balance' => 'integer',
            'type' => CreditTransactionType::class,
            'metadata' => 'array',
            'expires_at' => 'datetime',
        ];
    }
}

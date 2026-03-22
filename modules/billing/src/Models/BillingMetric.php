<?php

declare(strict_types=1);

namespace Modules\Billing\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Database\Factories\BillingMetricFactory;

/**
 * @property int $id
 * @property int $organization_id
 * @property \Carbon\Carbon $date
 * @property int $mrr
 * @property int $arr
 * @property int $new_subscriptions
 * @property int $churned
 * @property int $credits_purchased
 * @property int $credits_used
 */
final class BillingMetric extends Model
{
    use BelongsToOrganization;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'date',
        'mrr',
        'arr',
        'new_subscriptions',
        'churned',
        'credits_purchased',
        'credits_used',
    ];

    protected static function newFactory(): BillingMetricFactory
    {
        return BillingMetricFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'mrr' => 'integer',
            'arr' => 'integer',
            'new_subscriptions' => 'integer',
            'churned' => 'integer',
            'credits_purchased' => 'integer',
            'credits_used' => 'integer',
        ];
    }
}

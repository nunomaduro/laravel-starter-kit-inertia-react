<?php

declare(strict_types=1);

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $payment_gateway_id
 * @property int $plan_id
 * @property string $gateway_product_id
 * @property string|null $gateway_price_id
 */
final class GatewayProduct extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'payment_gateway_id',
        'plan_id',
        'gateway_product_id',
        'gateway_price_id',
    ];

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('laravel-subscriptions.models.plan'));
    }
}

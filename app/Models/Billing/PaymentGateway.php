<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Enums\Billing\PaymentGatewayType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property int $id
 * @property string $name
 * @property PaymentGatewayType $type
 * @property string|null $settings
 * @property bool $is_active
 * @property bool $is_default
 * @property array|null $supported_currencies
 * @property array|null $supported_payment_methods
 * @property int $sort_order
 */
final class PaymentGateway extends Model implements Sortable
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use SortableTrait;

    /** @var array<string, mixed> */
    public array $sortable = [
        'order_column_name' => 'sort_order',
    ];

    protected $fillable = [
        'name',
        'type',
        'settings',
        'is_active',
        'is_default',
        'supported_currencies',
        'supported_payment_methods',
        'sort_order',
    ];

    public function gatewayProducts(): HasMany
    {
        return $this->hasMany(GatewayProduct::class, 'payment_gateway_id');
    }

    public function getDecryptedSettings(): ?array
    {
        if ($this->settings === null) {
            return null;
        }

        return json_decode(Crypt::decryptString($this->settings), true, 512, JSON_THROW_ON_ERROR);
    }

    public function setEncryptedSettings(array $value): void
    {
        $this->settings = Crypt::encryptString(json_encode($value, JSON_THROW_ON_ERROR));
    }

    protected function casts(): array
    {
        return [
            'type' => PaymentGatewayType::class,
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'supported_currencies' => 'array',
            'supported_payment_methods' => 'array',
            'sort_order' => 'integer',
        ];
    }
}

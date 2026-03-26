---
title: Eloquent Attribute Casting
impact: HIGH
impactDescription: Automatic type conversion and data handling
tags: eloquent, casts, type-conversion, attributes
---

## Eloquent Attribute Casting

**Impact: HIGH (Automatic type conversion and data handling)**

Use Eloquent casts to automatically convert attributes to appropriate types.

## Bad Example

```php
// Manual type handling
class Order extends Model
{
    public function getMetadataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setMetadataAttribute($value)
    {
        $this->attributes['metadata'] = json_encode($value);
    }

    public function getIsPaidAttribute($value)
    {
        return (bool) $value;
    }

    public function getTotalAttribute($value)
    {
        return (float) $value;
    }
}

// In controller
$order = Order::find(1);
$isPaid = (bool) $order->is_paid;
$total = (float) $order->total;
$createdAt = Carbon::parse($order->created_at);
```

## Good Example

```php
// Built-in casts
namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Casts\AsStringable;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        // Primitives
        'is_paid' => 'boolean',
        'total' => 'decimal:2',
        'quantity' => 'integer',
        'rating' => 'float',

        // Arrays and JSON
        'metadata' => 'array',
        'settings' => 'json',
        'tags' => 'collection',

        // Dates
        'paid_at' => 'datetime',
        'shipped_date' => 'date',
        'created_at' => 'immutable_datetime',

        // Encrypted (automatically encrypts/decrypts)
        'secret_token' => 'encrypted',
        'api_keys' => 'encrypted:array',

        // Enums (PHP 8.1+)
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,

        // As object
        'address' => AsStringable::class,
    ];
}

// Usage - types are automatically converted
$order = Order::find(1);
$order->is_paid;        // bool
$order->total;          // "99.99" (decimal string)
$order->metadata;       // array
$order->paid_at;        // Carbon instance
$order->status;         // OrderStatus enum
```

```php
// Enums
enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Processing => 'blue',
            self::Shipped => 'purple',
            self::Delivered => 'green',
            self::Cancelled => 'red',
        };
    }
}
```

```php
// Custom cast class
namespace App\Casts;

use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{
    public function __construct(
        private string $currency = 'USD'
    ) {}

    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if (is_null($value)) {
            return null;
        }

        return new Money((int) $value, $this->currency);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof Money) {
            return $value->cents();
        }

        return (int) ($value * 100);
    }
}
```

```php
// Use custom cast
class Product extends Model
{
    protected $casts = [
        'price' => MoneyCast::class,
        'cost' => MoneyCast::class . ':EUR',
    ];
}
```

```php
// Cast with parameters via method (alternative to $casts property)
namespace App\Models;

use App\Casts\AddressCast;
use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'options' => AsCollection::class,
            'address' => AddressCast::class,
        ];
    }
}
```

```php
// Inbound-only casting (only on set)
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class HashCast implements CastsInboundAttributes
{
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Hash::make($value);
    }
}
```

## Why

- **Type safety**: Attributes are always the expected type
- **Less boilerplate**: No manual type conversions
- **Automatic serialization**: JSON encoding/decoding handled
- **Consistency**: Same behavior everywhere the attribute is used
- **Enum support**: Type-safe status fields with IDE support
- **Encryption**: Sensitive data encrypted at rest automatically
- **Custom logic**: Complex types handled via custom casts

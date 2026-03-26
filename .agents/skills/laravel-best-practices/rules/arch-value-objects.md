---
title: Value Objects
impact: MEDIUM
impactDescription: Enforces business rules and improves type safety
tags: architecture, value-objects, domain-driven, type-safety
---

## Value Objects

**Impact: MEDIUM (Enforces business rules and improves type safety)**

Encapsulate domain concepts with value objects to enforce business rules and improve type safety.

## Bad Example

```php
// Primitive obsession - using strings/numbers for domain concepts
class User extends Model
{
    public function setEmailAttribute(string $value): void
    {
        // Validation scattered across the codebase
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }
        $this->attributes['email'] = strtolower($value);
    }
}

class Order extends Model
{
    public function calculateTotal(): float
    {
        // Money as float - precision issues
        return $this->subtotal + $this->tax - $this->discount;
    }

    public function applyDiscount(float $amount): void
    {
        // No validation of negative values
        $this->discount = $amount;
    }
}

// Phone number without structure
$user->phone = '+1-555-123-4567';
// Later...
$cleanPhone = preg_replace('/[^0-9]/', '', $user->phone); // Manual parsing
```

## Good Example

```php
// Email value object
namespace App\ValueObjects;

use InvalidArgumentException;

readonly class Email
{
    public function __construct(
        private string $value
    ) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$value}");
        }
    }

    public function value(): string
    {
        return strtolower($this->value);
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function equals(Email $other): bool
    {
        return $this->value() === $other->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
```

```php
// Money value object with proper precision
namespace App\ValueObjects;

use InvalidArgumentException;

readonly class Money
{
    public function __construct(
        private int $cents,
        private string $currency = 'USD'
    ) {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money cannot be negative');
        }
    }

    public static function fromDollars(float $dollars, string $currency = 'USD'): self
    {
        return new self((int) round($dollars * 100), $currency);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function dollars(): float
    {
        return $this->cents / 100;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->cents + $other->cents, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->cents - $other->cents, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->cents * $factor), $this->currency);
    }

    public function format(): string
    {
        return number_format($this->dollars(), 2) . ' ' . $this->currency;
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }
}
```

```php
// Phone number value object
namespace App\ValueObjects;

readonly class PhoneNumber
{
    public function __construct(
        private string $countryCode,
        private string $number
    ) {}

    public static function fromString(string $phone): self
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($cleaned, '+')) {
            $countryCode = substr($cleaned, 0, 2);
            $number = substr($cleaned, 2);
        } else {
            $countryCode = '+1';
            $number = $cleaned;
        }

        return new self($countryCode, $number);
    }

    public function countryCode(): string
    {
        return $this->countryCode;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function format(): string
    {
        return sprintf('%s-%s-%s',
            $this->countryCode,
            substr($this->number, 0, 3),
            substr($this->number, 3)
        );
    }

    public function __toString(): string
    {
        return $this->countryCode . $this->number;
    }
}
```

```php
// Using value objects with Eloquent casts
namespace App\Casts;

use App\ValueObjects\Email;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class EmailCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Email
    {
        return $value ? new Email($value) : null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof Email) {
            return $value->value();
        }

        return $value ? (new Email($value))->value() : null;
    }
}
```

```php
// Model using value objects
class User extends Model
{
    protected $casts = [
        'email' => EmailCast::class,
    ];
}

// Usage
$user = new User();
$user->email = new Email('John@Example.com');
echo $user->email->domain(); // example.com

$price = Money::fromDollars(99.99);
$tax = $price->multiply(0.1);
$total = $price->add($tax);
echo $total->format(); // 109.99 USD
```

## Why

- **Encapsulation**: Business rules live with the data they validate
- **Type safety**: Cannot pass wrong type accidentally
- **Immutability**: Value objects are safer to pass around
- **Self-validating**: Invalid states cannot exist
- **Domain clarity**: Code speaks the language of the business
- **Reusability**: Same value object used consistently everywhere

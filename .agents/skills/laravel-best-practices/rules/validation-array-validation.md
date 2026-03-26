---
title: Array and Nested Validation
impact: HIGH
impactDescription: Validates complex nested data structures
tags: validation, arrays, nested-data
---

## Array and Nested Validation

**Impact: HIGH (Validates complex nested data structures)**

Validate arrays and nested data structures with Laravel's powerful array validation syntax.

## Bad Example

```php
// Manual array validation
class OrderController extends Controller
{
    public function store(Request $request)
    {
        // No proper array validation
        $items = $request->items;

        if (!is_array($items) || empty($items)) {
            return back()->withErrors(['items' => 'Items required']);
        }

        foreach ($items as $index => $item) {
            if (empty($item['product_id'])) {
                return back()->withErrors(["items.{$index}.product_id" => 'Product required']);
            }
            if (!is_numeric($item['quantity']) || $item['quantity'] < 1) {
                return back()->withErrors(["items.{$index}.quantity" => 'Invalid quantity']);
            }
        }

        // Prone to errors, hard to maintain
    }
}
```

## Good Example

```php
// Proper array validation
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Array validation
            'items' => ['required', 'array', 'min:1', 'max:50'],

            // Validate each item in the array
            'items.*' => ['required', 'array'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],

            // Nested arrays
            'items.*.options' => ['nullable', 'array'],
            'items.*.options.*' => ['string', 'max:100'],

            // Shipping address as object
            'shipping' => ['required', 'array'],
            'shipping.street' => ['required', 'string', 'max:255'],
            'shipping.city' => ['required', 'string', 'max:100'],
            'shipping.state' => ['required', 'string', 'size:2'],
            'shipping.zip' => ['required', 'string', 'regex:/^\d{5}(-\d{4})?$/'],
            'shipping.country' => ['required', 'string', 'size:2'],

            // Optional billing address
            'billing' => ['nullable', 'array'],
            'billing.street' => ['required_with:billing', 'string', 'max:255'],
            'billing.city' => ['required_with:billing', 'string', 'max:100'],

            // Tags array
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50', 'distinct'],

            // Key-value metadata
            'metadata' => ['nullable', 'array'],
            'metadata.*.key' => ['required', 'string', 'max:50'],
            'metadata.*.value' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Please add at least one item to your order.',
            'items.min' => 'Your order must contain at least one item.',
            'items.*.product_id.required' => 'Each item must have a product selected.',
            'items.*.product_id.exists' => 'Item #:position has an invalid product.',
            'items.*.quantity.min' => 'Item #:position quantity must be at least 1.',
            'tags.*.distinct' => 'Duplicate tags are not allowed.',
        ];
    }
}
```

```php
// Validating array keys
class ConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Only allow specific keys
            'settings' => ['required', 'array'],
            'settings.theme' => ['required', Rule::in(['light', 'dark', 'auto'])],
            'settings.language' => ['required', 'string', 'size:2'],
            'settings.notifications' => ['required', 'array'],
            'settings.notifications.email' => ['required', 'boolean'],
            'settings.notifications.push' => ['required', 'boolean'],
            'settings.notifications.sms' => ['required', 'boolean'],
        ];
    }
}
```

```php
// Dynamic array keys validation
class DynamicFormRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'fields' => ['required', 'array'],
        ];

        // Add rules for each field dynamically
        foreach ($this->input('fields', []) as $key => $value) {
            if (str_starts_with($key, 'email_')) {
                $rules["fields.{$key}"] = ['required', 'email'];
            } elseif (str_starts_with($key, 'phone_')) {
                $rules["fields.{$key}"] = ['required', 'string', 'min:10'];
            }
        }

        return $rules;
    }
}
```

```php
// Validating file arrays
class GalleryUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,png,webp', 'max:5120'],

            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'],

            // With metadata for each file
            'files' => ['required', 'array'],
            'files.*.file' => ['required', 'file', 'max:10240'],
            'files.*.title' => ['required', 'string', 'max:100'],
            'files.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
```

```php
// Custom array validation rule
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueArrayValues implements ValidationRule
{
    public function __construct(
        private string $key
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            return;
        }

        $values = collect($value)->pluck($this->key);

        if ($values->count() !== $values->unique()->count()) {
            $fail("The {$attribute} contains duplicate {$this->key} values.");
        }
    }
}
```

```php
// Usage
'items' => ['required', 'array', new UniqueArrayValues('product_id')],
```

## Why

- **Type safety**: Ensures arrays contain expected data types
- **Nested validation**: Deep validation of complex structures
- **Custom messages**: Clear error messages with array positions
- **Consistency**: Same validation syntax for all array types
- **Security**: Prevents unexpected data from entering the system
- **Documentation**: Rules document expected data structure

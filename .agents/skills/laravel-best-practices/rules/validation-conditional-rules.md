---
title: Conditional Validation Rules
impact: HIGH
impactDescription: Dynamic validation based on input
tags: validation, conditional, dynamic-rules
---

## Conditional Validation Rules

**Impact: HIGH (Dynamic validation based on input)**

Apply validation rules conditionally based on input values or other conditions.

## Bad Example

```php
// Manual conditional logic in controller
class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:card,bank,paypal',
        ];

        // Messy conditional rules
        if ($request->payment_method === 'card') {
            $rules['card_number'] = 'required|string|size:16';
            $rules['card_expiry'] = 'required|date_format:m/y';
            $rules['card_cvv'] = 'required|string|size:3';
        }

        if ($request->payment_method === 'bank') {
            $rules['bank_account'] = 'required|string';
            $rules['routing_number'] = 'required|string|size:9';
        }

        if ($request->payment_method === 'paypal') {
            $rules['paypal_email'] = 'required|email';
        }

        if ($request->save_for_later) {
            $rules['nickname'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules);

        // ...
    }
}
```

## Good Example

```php
// Form Request with conditional rules
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['card', 'bank', 'paypal'])],

            // Conditional with required_if
            'card_number' => ['required_if:payment_method,card', 'nullable', 'digits:16'],
            'card_expiry' => ['required_if:payment_method,card', 'nullable', 'date_format:m/y'],
            'card_cvv' => ['required_if:payment_method,card', 'nullable', 'digits:3'],

            'bank_account' => ['required_if:payment_method,bank', 'nullable', 'string'],
            'routing_number' => ['required_if:payment_method,bank', 'nullable', 'digits:9'],

            'paypal_email' => ['required_if:payment_method,paypal', 'nullable', 'email'],

            // Required when another field is truthy
            'save_for_later' => ['boolean'],
            'nickname' => ['required_if:save_for_later,true', 'nullable', 'string', 'max:50'],
        ];
    }
}
```

```php
// Using Rule::when() for complex conditions
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['standard', 'express', 'scheduled'])],
            'items' => ['required', 'array', 'min:1'],

            // When type is 'scheduled', require delivery date
            'delivery_date' => [
                Rule::when(
                    $this->type === 'scheduled',
                    ['required', 'date', 'after:today'],
                    ['nullable', 'date']
                ),
            ],

            // Complex condition with closure
            'express_fee_accepted' => [
                Rule::when(
                    fn () => $this->type === 'express' && $this->total > 100,
                    ['required', 'accepted'],
                ),
            ],

            // Exclude when condition is true
            'coupon_code' => [
                Rule::excludeIf($this->type === 'express'),
                'nullable',
                'string',
                'exists:coupons,code',
            ],
        ];
    }
}
```

```php
// Sometimes for dynamic conditions
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'newsletter' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->sometimes('phone', 'required|string', function ($input) {
            return $input->newsletter === true;
        });
    }
}
```

```php
// Conditional rules in controller (when needed)
class RegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_type' => ['required', Rule::in(['personal', 'business'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],

            // Business account requires additional fields; prohibited for personal
            'company_name' => [
                'required_if:account_type,business',
                'nullable',
                'string',
                Rule::prohibitedIf($request->account_type === 'personal'),
            ],
            'tax_id' => ['required_if:account_type,business', 'nullable', 'string'],
        ]);
    }
}
```

```php
// Multiple conditions
class ComplexFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'has_children' => ['boolean'],
            'children_count' => [
                'required_if:has_children,true',
                'nullable',
                'integer',
                'min:1',
                'max:20',
            ],

            // Required unless another field has a value
            'emergency_contact' => ['required_unless:has_spouse,true'],

            // Required without another field
            'mobile_phone' => ['required_without:home_phone'],
            'home_phone' => ['required_without:mobile_phone'],

            // Required with all of these fields
            'delivery_notes' => ['required_with_all:street,city,zip'],

            // Exclude if null or missing
            'metadata' => ['exclude_if:type,basic', 'array'],
        ];
    }
}
```

## Why

- **Clean code**: No manual if/else for building rules
- **Declarative**: Rules clearly state their conditions
- **Built-in support**: Laravel provides many conditional rules
- **Flexible**: Complex conditions supported with closures
- **Maintainable**: Conditions live with their rules
- **Testable**: Conditional logic tested through form requests

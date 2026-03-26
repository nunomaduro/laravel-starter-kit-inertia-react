---
title: Custom Validation Rules
impact: HIGH
impactDescription: Reusable domain-specific validation
tags: validation, custom-rules, reusability
---

## Custom Validation Rules

**Impact: HIGH (Reusable domain-specific validation)**

Create reusable custom validation rules for complex or domain-specific validation logic.

## Bad Example

```php
// Complex validation logic in controller
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    // Complex password rules inline
                    if (!preg_match('/[A-Z]/', $value)) {
                        $fail('Password must contain uppercase letter.');
                    }
                    if (!preg_match('/[a-z]/', $value)) {
                        $fail('Password must contain lowercase letter.');
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        $fail('Password must contain a number.');
                    }
                    if (!preg_match('/[@$!%*#?&]/', $value)) {
                        $fail('Password must contain a special character.');
                    }
                    if (preg_match('/(.)\1{2,}/', $value)) {
                        $fail('Password cannot contain 3+ repeated characters.');
                    }
                },
            ],
            'phone' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Phone validation duplicated everywhere
                    $cleaned = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($cleaned) < 10 || strlen($cleaned) > 15) {
                        $fail('Invalid phone number.');
                    }
                },
            ],
        ]);
    }
}
```

## Good Example

```php
// Custom rule class for strong password
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function __construct(
        private bool $requireUppercase = true,
        private bool $requireNumber = true,
        private bool $requireSpecialChar = true,
        private int $minLength = 8,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < $this->minLength) {
            $fail("The :attribute must be at least {$this->minLength} characters.");
            return;
        }

        if ($this->requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain at least one uppercase letter.');
        }

        if (!preg_match('/[a-z]/', $value)) {
            $fail('The :attribute must contain at least one lowercase letter.');
        }

        if ($this->requireNumber && !preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must contain at least one number.');
        }

        if ($this->requireSpecialChar && !preg_match('/[@$!%*#?&]/', $value)) {
            $fail('The :attribute must contain at least one special character.');
        }

        if (preg_match('/(.)\1{2,}/', $value)) {
            $fail('The :attribute cannot contain 3 or more repeated characters.');
        }
    }
}
```

```php
// Phone number validation rule
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhoneNumber implements ValidationRule
{
    public function __construct(
        private ?string $countryCode = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $value);

        if (strlen($cleaned) < 10 || strlen($cleaned) > 15) {
            $fail('The :attribute must be a valid phone number.');
            return;
        }

        if ($this->countryCode === 'US' && !preg_match('/^(\+1)?[0-9]{10}$/', $cleaned)) {
            $fail('The :attribute must be a valid US phone number.');
        }
    }
}
```

```php
// Rule with database check
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueForUser implements ValidationRule
{
    public function __construct(
        private string $table,
        private string $column,
        private ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where('user_id', auth()->id());

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("You already have a record with this :attribute.");
        }
    }
}
```

```php
// Invokable rule for simple cases
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Uppercase implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtoupper($value) !== $value) {
            $fail('The :attribute must be uppercase.');
        }
    }
}
```

```php
// Using custom rules in Form Request
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                new StrongPassword(
                    requireSpecialChar: true,
                    minLength: 12
                ),
            ],
            'phone' => ['required', new ValidPhoneNumber('US')],
            'nickname' => [
                'nullable',
                'string',
                new UniqueForUser('profiles', 'nickname'),
            ],
        ];
    }
}
```

```php
// Register as a fluent macro (optional)
// In AppServiceProvider
// Or use Rule::macro for fluent syntax
Rule::macro('strongPassword', function () {
    return new StrongPassword();
});

// Usage: 'password' => ['required', Rule::strongPassword()]
```

## Why

- **Reusability**: Same validation logic used across the application
- **Testability**: Custom rules can be unit tested
- **Encapsulation**: Complex logic hidden behind simple interface
- **Readability**: Clean, descriptive rule names
- **Maintainability**: Change validation logic in one place
- **Documentation**: Rule classes document validation requirements

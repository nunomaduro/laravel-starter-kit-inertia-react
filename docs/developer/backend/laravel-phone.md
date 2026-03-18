# Laravel Phone (propaganistas/laravel-phone)

Phone number validation, formatting, and casting using [libphonenumber](https://github.com/google/libphonenumber) (via giggsey/libphonenumber-for-php-lite).

## Validation

Use the `phone` rule in validation. Add allowed country codes or `INTERNATIONAL` for any valid international number.

```php
// Any valid international number
'phone' => ['nullable', 'phone:INTERNATIONAL'],

// Specific countries (ISO 3166-1 alpha-2)
'phone' => ['required', 'phone:US,BE'],

// With country from another field (e.g. phone_country)
'phone' => ['required', 'phone'],
'phone_country' => ['required_with:phone'],

// Mobile only
'phone' => ['phone:mobile,US'],

// Lenient (length check only)
'phone' => ['phone:LENIENT'],
```

**Translation:** Add to `lang/en/validation.php` (and other locales):

```php
'phone' => 'The :attribute field must be a valid phone number.',
```

## Attribute casting

Cast Eloquent attributes to `PhoneNumber` objects:

```php
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;

// E164PhoneNumberCast: stores E.164 format (+3212345678)
'phone' => E164PhoneNumberCast::class.':BE',

// RawPhoneNumberCast: stores raw input
'phone' => RawPhoneNumberCast::class.':phone_country',
```

**Order matters for E164PhoneNumberCast:** Set `phone_country` before `phone` when filling, so the cast can parse local formats.

## Utility

```php
use Propaganistas\LaravelPhone\PhoneNumber;

// Format to E.164
(string) new PhoneNumber('+3212/34.56.78');           // +3212345678
(string) new PhoneNumber('012 34 56 78', 'BE');      // +3212345678

// phone() helper
phone('+3212/34.56.78');                    // PhoneNumber instance
phone('012 34 56 78', 'BE', $format);       // formatted string
```

## Usage in this app

- **StoreEnterpriseInquiryRequest:** `phone` validated with `phone:INTERNATIONAL` (optional).
- **User, EnterpriseInquiry:** `phone` column; no cast by default (stored as string).

## Reference

- [GitHub](https://github.com/Propaganistas/Laravel-Phone)
- [Packagist](https://packagist.org/packages/propaganistas/laravel-phone)

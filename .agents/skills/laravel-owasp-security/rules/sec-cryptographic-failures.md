---
title: Prevent Cryptographic Failures
impact: CRITICAL
impactDescription: Prevents plaintext credential storage and sensitive data exposure
tags: security, cryptography, hashing, encryption, owasp-a02
---

## Prevent Cryptographic Failures

**Impact: CRITICAL (Prevents plaintext credential storage and sensitive data exposure)**

## Why It Matters

- **Risk**: Plaintext or weakly hashed passwords are trivially cracked on any data breach. Unencrypted API keys in the database are exposed to any SQL dump
- **Impact**: Full account takeover, payment gateway compromise, third-party service abuse
- **OWASP**: A02:2021 — covers all forms of weak, missing, or misapplied cryptography

## Incorrect

```php
<?php

// ❌ Password stored as plaintext
class RegisteredUserController extends Controller
{
    public function store(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,  // Plaintext — NEVER do this
        ]);
    }
}
```

```php
<?php

// ❌ Weak MD5 or SHA1 hashing — trivially cracked via rainbow tables
$hashedPassword = md5($request->password);
$hashedPassword = sha1($request->password);
```

```php
<?php

// ❌ API secret stored as plaintext in the database
Setting::set('toyyibpay_secret_key', $request->secret_key);  // Plaintext in DB
// Now any SQL dump or DB read exposes the secret key
```

```php
<?php

// ❌ Sensitive one-time link without signing — ID can be tampered
Route::get('/email/verify/{id}', function ($id) {
    User::findOrFail($id)->markEmailAsVerified();
});
// Attacker can verify any account by guessing or incrementing the ID
```

**Problems:**
- Plaintext passwords are exposed in any database breach
- MD5/SHA1 hashes are pre-computed in rainbow tables and cracked in seconds
- Plaintext API keys in the DB are exposed in any DB dump or admin query
- Unsigned verification links allow account takeover by ID manipulation

## Correct

### Always Hash Passwords with Bcrypt

```php
<?php

declare(strict_types=1);

// ✅ Use Laravel's 'hashed' cast — automatically bcrypt on set
class User extends Authenticatable
{
    protected $casts = [
        'password' => 'hashed',  // Automatically hashed via Hash::make()
    ];
}

// ✅ Or use Hash::make() explicitly
User::create([
    'name'     => $request->name,
    'email'    => $request->email,
    'password' => Hash::make($request->password),
]);
```

### Encrypt Sensitive Fields at Rest

```php
<?php

declare(strict_types=1);

// ✅ Use Laravel's 'encrypted' cast for sensitive DB columns
class Setting extends Model
{
    protected $casts = [
        'value' => 'encrypted',  // Auto-encrypts using APP_KEY via AES-256-CBC
    ];
}

// ✅ Or use Crypt facade manually
Setting::set('toyyibpay_secret_key', Crypt::encryptString($request->secret_key));

// Decrypt on read
$secretKey = Crypt::decryptString(Setting::get('toyyibpay_secret_key'));
```

### Use Signed URLs for Sensitive One-Time Actions

```php
<?php

declare(strict_types=1);

// ✅ Signed URL for email verification — cannot be tampered
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

// ✅ Generate signed URL with expiry
$url = URL::temporarySignedRoute(
    'verification.verify',
    now()->addMinutes(60),
    ['id' => $user->id, 'hash' => sha1($user->email)],
);

// ✅ Generate signed URL for password reset
$url = URL::signedRoute('password.reset', ['token' => $token, 'email' => $email]);
```

### Mask Secrets in UI — Never Send Full Keys to Frontend

```php
<?php

declare(strict_types=1);

// ✅ Mask secret in Inertia prop — frontend only needs to know if key is set
class SettingsController extends Controller
{
    public function index(): Response
    {
        $secretKey = Crypt::decryptString(Setting::get('toyyibpay_secret_key', ''));

        return Inertia::render('admin/settings/index', [
            'toyyibpay' => [
                'secret_key_set'    => $secretKey !== '',
                'secret_key_masked' => $secretKey !== ''
                    ? substr($secretKey, 0, 4) . str_repeat('*', 12)
                    : '',
            ],
        ]);
    }
}
```

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `'password' => 'hashed'` cast | User password fields |
| `'value' => 'encrypted'` cast | API keys, secrets, tokens in DB |
| `Crypt::encryptString()` / `decryptString()` | Manual encrypt/decrypt |
| `URL::signedRoute()` | Password reset, email verify links |
| `URL::temporarySignedRoute()` | Time-limited one-time links |

Reference: [Laravel Encryption](https://laravel.com/docs/13.x/encryption) | [OWASP A02:2021](https://owasp.org/Top10/A02_2021-Cryptographic_Failures/)

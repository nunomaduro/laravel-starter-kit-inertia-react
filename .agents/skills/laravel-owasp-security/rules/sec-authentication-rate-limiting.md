---
title: Enforce Authentication and Rate Limiting
impact: HIGH
impactDescription: Prevents brute force, credential stuffing, and session fixation attacks
tags: security, authentication, rate-limiting, throttle, session, owasp-a07
---

## Enforce Authentication and Rate Limiting

**Impact: HIGH (Prevents brute force, credential stuffing, and session fixation attacks)**

## Why It Matters

- **Risk**: Without rate limiting, attackers can attempt thousands of passwords per minute. Without session regeneration, session fixation allows an attacker to set a victim's session ID before login and hijack it after
- **Impact**: Account takeover, credential stuffing at scale, session hijacking
- **OWASP**: A07:2021 — Identification and Authentication Failures

## Incorrect — No Rate Limiting

```php
<?php

// ❌ Login route with no throttle — allows unlimited attempts
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// ❌ Password reset with no throttle — enumerable via timing
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);

// ❌ Payment route with no throttle — allows mass submission
Route::post('registrations/{registration}/payment', [PaymentController::class, 'store']);
```

```php
<?php

// ❌ Custom auth that doesn't hash passwords
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)
                    ->where('password', $request->password)  // Plaintext comparison!
                    ->first();
    }
}
```

```php
<?php

// ❌ No session regeneration after login — session fixation vulnerability
class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        // Missing session()->regenerate() — same session ID before and after login
        return redirect()->intended(route('dashboard'));
    }
}
```

**Problems:**
- No throttle allows unlimited login attempts — brute force and credential stuffing are trivial
- No session regeneration after login allows session fixation — attacker sets victim's session ID and takes over after they log in
- Plaintext password comparison bypasses all hashing protections

## Correct — Rate Limiting on Auth Routes

```php
<?php

// ✅ Throttle middleware on sensitive routes
Route::post('/', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');
// Note: throttle is handled inside LoginRequest via RateLimiter

// ✅ Throttle on password reset
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('password.email');

// ✅ Throttle on email verification
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// ✅ Throttle on payment submission
Route::post('registrations/{registration}/payment', [PaymentController::class, 'store'])
    ->middleware(['auth', 'verified', 'role:student', 'throttle:10,1'])
    ->name('student.payments.store');

// ✅ Throttle on webhook callbacks
Route::post('webhooks/toyyibpay', [ToyyibPayController::class, 'callback'])
    ->middleware('throttle:60,1');
```

### Use RateLimiter in LoginRequest

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', ['seconds' => $seconds]),
        ]);
    }

    public function throttleKey(): string
    {
        // Key combines email + IP — prevents per-IP bypass
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
```

### Always Regenerate Session After Login

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // ✅ Regenerate session ID — prevents session fixation
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // ✅ Invalidate and regenerate on logout too
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

### Session Configuration

```php
<?php

// ✅ config/session.php — secure session settings
return [
    'lifetime'  => env('SESSION_LIFETIME', 30),  // 30 min — not 120
    'http_only' => true,
    'same_site' => 'lax',
    'secure'    => env('SESSION_SECURE_COOKIE'),  // Auto-true on HTTPS
    'domain'    => null,
];
```

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `RateLimiter::hit()` in `LoginRequest` | Login brute force prevention |
| `throttle:5,1` middleware | Password reset, email verify routes |
| `throttle:10,1` middleware | Payment and sensitive action routes |
| `session()->regenerate()` | Always call after successful login |
| `session()->invalidate()` | Always call on logout |
| `SESSION_LIFETIME=30` | Reduce idle session window |

Reference: [Laravel Authentication](https://laravel.com/docs/13.x/authentication) | [Laravel Rate Limiting](https://laravel.com/docs/13.x/rate-limiting)

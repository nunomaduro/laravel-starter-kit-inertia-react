# Laravel OWASP Security — Full Compiled Reference

OWASP Top 10 security audit checklist and secure coding reference for Laravel 13 + React/Inertia.js applications.

**Version:** 1.0.3 | **Laravel:** 13.x | **PHP:** 8.3+ | **Author:** AsyrafHussin

---

## How to Run an Audit

### Step 1: Detect Stack

Check if the project uses React + Inertia.js:
- `app/Http/Middleware/HandleInertiaRequests.php` exists
- `resources/js/` contains `.tsx` or `.jsx` files
- `inertiajs/inertia-laravel` in `composer.json`
- `@inertiajs/react` in `package.json`

State at the top of report:
- **Detected**: "React + Inertia.js detected — Laravel OWASP checklist AND React/Inertia security checks will both be applied."
- **Not detected**: "No React/Inertia.js detected — applying Laravel OWASP checklist only."

### Step 2: Scope

- Arguments provided: review only those files/features
- No arguments: review entire codebase

### Step 3: Output Format

- **PASS** `file:line` — brief confirmation
- **FAIL** `file:line` — description of the vulnerability (do NOT reproduce any code, values, API keys, tokens, or .env contents from the file) + fix recommendation
- **N/A** — not applicable to this project

---

## Section 1: Broken Access Control (A01:2021) — CRITICAL

The most common critical vulnerability. Attackers access other users' records, perform admin actions, or escalate privileges by manipulating IDs or bypassing role checks.

### Checklist

- [ ] Middleware protects all route groups by role (`auth`, `role:admin`, etc.)
- [ ] Resource queries scoped to authenticated user — `->where('user_id', auth()->id())`
- [ ] No direct object reference without ownership check
- [ ] Gates and Policies used to authorize resource access
- [ ] Frontend role checks are mirrored server-side — never rely on React UI checks alone

### Incorrect

```php
// ❌ No ownership check — any user can view any payment by changing the ID
$payment = Payment::find($id);

// ❌ Admin routes without middleware
Route::prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

### Correct

```php
// ✅ Always scope to authenticated user
$payment = Payment::where('user_id', auth()->id())->findOrFail($id);

// ✅ Role middleware on all admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('users', UserController::class);
});

// ✅ Policy-based authorization
$this->authorize('view', $payment);
```

---

## Section 2: Cryptographic Failures (A02:2021) — CRITICAL

Weak or missing cryptography exposes passwords, API keys, and sensitive data on any breach.

### Checklist

- [ ] Passwords hashed with `Hash::make()` or `'hashed'` Eloquent cast
- [ ] No MD5 or SHA1 for password hashing
- [ ] Sensitive fields encrypted with `Crypt::encryptString()` or `'encrypted'` cast
- [ ] `APP_KEY` is long, random, and unique per environment
- [ ] Signed URLs used for password reset, email verification

### Incorrect

```php
// ❌ Plaintext password
User::create(['password' => $request->password]);

// ❌ Weak hashing
$hash = md5($request->password);

// ❌ API key in plaintext DB column
Setting::set('toyyibpay_secret_key', $request->secret_key);
```

### Correct

```php
// ✅ Hashed cast on User model
protected $casts = ['password' => 'hashed'];

// ✅ Encrypted cast for sensitive DB columns
protected $casts = ['value' => 'encrypted'];

// ✅ Signed URL for email verification
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');
```

---

## Section 3: Injection (A03:2021) — CRITICAL

SQL injection allows database exfiltration. Mass assignment allows setting fields like `is_admin`. XSS (also A03) allows script execution via user-supplied content.

### Checklist

**SQL & Mass Assignment:**
- [ ] No string concatenation in `whereRaw()`, `selectRaw()`, `orderByRaw()`
- [ ] Column names from user input whitelist-validated
- [ ] No `$request->all()` in `create()`, `fill()`, `update()`
- [ ] No `forceFill()` with unvalidated input
- [ ] Models define `$fillable` explicitly
- [ ] Controllers use `$request->validated()`

**XSS — Blade & React:**
- [ ] No `{!! $userInput !!}` in Blade with untrusted data
- [ ] No `dangerouslySetInnerHTML` without `DOMPurify.sanitize()`
- [ ] `href`/`src` not set from unvalidated user input
- [ ] No `eval()` or `new Function()` with user-controlled strings

### Incorrect

```php
// ❌ SQL injectable
$classes = DB::select("SELECT * FROM classes ORDER BY {$request->sort}");

// ❌ Mass assignment
User::create($request->all());

// ❌ $guarded = []
protected $guarded = [];
```

```tsx
// ❌ Raw HTML without sanitization — stored XSS
<div dangerouslySetInnerHTML={{ __html: sessionNote.notes }} />

// ❌ Unvalidated href — javascript: URLs execute on click
<a href={user.website}>Link</a>
```

### Correct

```php
// ✅ Parameterized bindings
$classes = Class::whereRaw('name LIKE ?', ['%' . $request->search . '%'])->get();

// ✅ Whitelist for dynamic columns
$column = in_array($request->column, ['name', 'price', 'created_at'])
    ? $request->column : 'created_at';

// ✅ Use validated() only
Post::create([...$request->validated(), 'user_id' => auth()->id()]);

// ✅ Explicit $fillable
protected $fillable = ['title', 'body', 'category_id'];
```

```tsx
import DOMPurify from 'dompurify';

// ✅ Always sanitize
<div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(sessionNote.notes) }} />

// ✅ Validate URL scheme
const safeUrl = url.startsWith('https://') || url.startsWith('http://') ? url : '#';
<a href={safeUrl}>Link</a>
```

---

## Section 4: Insecure Design (A04:2021) — HIGH

Missing server-side business logic controls allow clients to manipulate prices, bypass payments, or perform unauthorized bulk operations.

### Checklist

- [ ] Business logic enforced server-side — prices, totals, and discounts never trusted from client input
- [ ] Sensitive operations require secondary confirmation (e.g. password re-entry for account deletion)
- [ ] No mass action endpoints without per-item authorization check
- [ ] Admin-only features isolated behind separate middleware — not just hidden in the UI
- [ ] Payment amounts and enrollment states calculated server-side, not passed as form inputs

### Incorrect

```php
// ❌ Trusting client-supplied price
$amount = $request->input('amount');
Payment::create(['amount' => $amount, 'user_id' => auth()->id()]);

// ❌ Admin check only in frontend
// React: {isAdmin && <DeleteButton />}  — no server-side gate
Route::delete('/users/{user}', [UserController::class, 'destroy']); // No middleware
```

### Correct

```php
// ✅ Always calculate server-side
$registration = Registration::where('user_id', auth()->id())->findOrFail($id);
$amount = $registration->remaining_balance; // Derived from DB, never from request

// ✅ Gate on every destructive route
Route::middleware(['auth', 'role:admin'])
    ->delete('/users/{user}', [UserController::class, 'destroy']);
```

---

## Section 5: Security Misconfiguration (A05:2021) — HIGH

Misconfigured environments expose stack traces and leave browser protections disabled.

### Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `.env` in `.gitignore` and never committed
- [ ] Database uses a restricted user — not root/admin
- [ ] `APP_KEY` set and unique per environment
- [ ] CORS `allowed_origins` not `['*']` for authenticated routes
- [ ] Security headers middleware active (CSP, X-Frame-Options, HSTS, etc.)

### Correct

```php
// .env (production)
APP_ENV=production
APP_DEBUG=false
DB_USERNAME=app_user  // Not root

// config/cors.php
'allowed_origins' => ['https://yourdomain.com'],

// SecurityHeaders middleware — CSP with nonce
$nonce = Vite::useCspNonce();
$response->headers->set('Content-Security-Policy', implode('; ', [
    "default-src 'self'",
    "script-src 'self' 'nonce-{$nonce}'",
    "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
    "object-src 'none'",
    "frame-ancestors 'none'",
]));
```

---

## Section 6: Vulnerable & Outdated Components (A06:2021) — MEDIUM

### Checklist

- [ ] `composer audit` passes with no known CVEs
- [ ] `npm audit` passes with no known CVEs
- [ ] Laravel framework on supported version

```bash
composer audit
npm audit
```

---

## Section 7: Identification & Authentication Failures (A07:2021) — HIGH

Brute force and session fixation are prevented by throttling and session regeneration. Cookie misconfiguration allows session theft.

### Checklist

**Auth:**
- [ ] Login throttled via `RateLimiter` in `LoginRequest`
- [ ] Password reset and email verify routes throttled
- [ ] Payment routes throttled (`throttle:10,1`)
- [ ] `session()->regenerate()` called after login
- [ ] `session()->invalidate()` called on logout
- [ ] `SESSION_LIFETIME` set to 30 minutes or less

**Cookie & Session:**
- [ ] `http_only = true` in `config/session.php`
- [ ] `same_site = lax` or `strict`
- [ ] `secure = null` or `true` (HTTPS only)
- [ ] `lifetime = 30` (not 120)
- [ ] `EncryptCookies` middleware active

### Correct

```php
// ✅ Throttle on routes
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1']);

Route::post('registrations/{registration}/payment', [PaymentController::class, 'store'])
    ->middleware(['auth', 'verified', 'throttle:10,1']);

// ✅ Session regeneration
public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();  // Prevent session fixation
    return redirect()->intended(route('dashboard'));
}

// ✅ RateLimiter in LoginRequest (5 attempts per minute per email+IP)
public function throttleKey(): string
{
    return Str::lower($this->string('email')) . '|' . $this->ip();
}
```

---

## Section 8: Software & Data Integrity Failures (A08:2021) — HIGH

CSRF allows attackers to forge state-changing requests. Unsafe deserialization allows remote code execution.

### Checklist

**CSRF:**
- [ ] `VerifyCsrfToken` active in web group
- [ ] Only external webhook routes excluded
- [ ] `@csrf` in all Blade forms
- [ ] Inertia `X-XSRF-TOKEN` not disabled

**Deserialization:**
- [ ] No `unserialize($request->input(...))`
- [ ] No `eval($request->input(...))`
- [ ] No `extract($request->all())`

### Correct

```php
// ✅ Only exclude external webhooks
$middleware->validateCsrfTokens(except: ['/webhooks/toyyibpay']);
```

```tsx
// ✅ Inertia handles CSRF automatically
router.post('/profile', data);
const { post } = useForm(data);
post('/profile');
```

---

## Section 9: Security Logging & Monitoring Failures (A09:2021) — MEDIUM

### Checklist

- [ ] Failed login attempts logged
- [ ] Payment failures and exceptions logged
- [ ] No raw passwords or secrets in log entries
- [ ] Monitoring in place (Telescope, Sentry, or similar)

---

## Section 10: Server-Side Request Forgery — SSRF (A10:2021) — HIGH

### Checklist

- [ ] No `Http::get($request->input('url'))` with unvalidated URLs
- [ ] User-supplied URLs validated against allowlist or scheme check

```php
// ❌ SSRF vulnerability
Http::get($request->input('url'));

// ✅ Allowlist-validated
$allowedHosts = ['api.toyyibpay.com', 'sandbox.toyyibpay.com'];
$host = parse_url($url, PHP_URL_HOST);
abort_unless(in_array($host, $allowedHosts), 422, 'Invalid URL.');
Http::get($url);
```

---

## Additional Checks

> Not part of the OWASP Top 10 but critical for Laravel applications.

### Command Injection & Dangerous Functions

- [ ] No `exec()`, `shell_exec()`, `system()` with user input
- [ ] No open redirects via user-supplied URLs
- [ ] File uploads validate `mimes:` and `max:` — filenames not from raw user input

### Security Headers

- [ ] `Content-Security-Policy` with nonces
- [ ] `X-Frame-Options` set
- [ ] `X-Content-Type-Options` set
- [ ] `Strict-Transport-Security` set
- [ ] `Referrer-Policy` set
- [ ] `Permissions-Policy` set

---

## React + Inertia.js Checks

### R1. XSS in React Components

- [ ] No `dangerouslySetInnerHTML` without `DOMPurify.sanitize()`
- [ ] `href`/`src` not set from unvalidated user input
- [ ] No `eval()` or `new Function()` with user-controlled strings
- [ ] Links validate scheme (`https://` or `http://` only)

### R2. Inertia.js Data Exposure — CRITICAL

- [ ] `HandleInertiaRequests::share()` exposes no passwords, tokens, or internal flags
- [ ] Controllers use `->only([...])` or API Resources — not raw `toArray()`
- [ ] Secret keys and admin credentials never passed as Inertia props
- [ ] Inertia v2 History Encryption enabled for sensitive pages

```php
// ❌ Secret key in data-page HTML
'secret_key' => Setting::get('toyyibpay_secret_key')

// ✅ Masked — frontend only needs to know if it is set
'secret_key_set'    => $secretKey !== '',
'secret_key_masked' => substr($secretKey, 0, 4) . str_repeat('*', 12),
```

### R3. CSRF in Inertia.js

- [ ] `X-XSRF-TOKEN` not disabled
- [ ] Custom `fetch` calls include CSRF token
- [ ] Webhook routes are the ONLY CSRF exclusions

### R4. Authentication State in React

- [ ] `auth.user` prop excludes password hash, remember tokens, 2FA secrets
- [ ] Role checks enforced server-side
- [ ] `auth.user` contains only fields the UI needs

```php
// ✅ Minimal shared auth props
'user' => [
    'id'           => $request->user()->id,
    'name'         => $request->user()->name,
    'email'        => $request->user()->email,
    'current_role' => $request->user()->currentRole?->only(['id', 'name', 'slug']),
]
```

### R5. Sensitive Data in Browser

- [ ] No API keys hardcoded in React/TypeScript files
- [ ] No sensitive data in `localStorage`/`sessionStorage`
- [ ] `VITE_*` vars contain no secrets

### R6. Dependency Security

- [ ] `npm audit` passes
- [ ] React on supported version
- [ ] Third-party libraries reviewed for CVEs

---

## Quick Audit Commands

```bash
# Dependency vulnerabilities
composer audit
npm audit

# Search for dangerous patterns
grep -r "dangerouslySetInnerHTML" resources/js --include="*.tsx" | grep -v "DOMPurify"
grep -r "\$request->all()" app/Http/Controllers
grep -r "whereRaw\|orderByRaw\|selectRaw" app --include="*.php"
grep -r "eval\|unserialize\|extract\(" app --include="*.php"
grep -r "guarded = \[\]" app/Models
```

---

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Laravel Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html)
- [Laravel 13 Authentication](https://laravel.com/docs/13.x/authentication)
- [Laravel 13 Authorization](https://laravel.com/docs/13.x/authorization)
- [Laravel 13 Encryption](https://laravel.com/docs/13.x/encryption)
- [Laravel 13 CSRF](https://laravel.com/docs/13.x/csrf)
- [Inertia.js Security](https://inertiajs.com/security)
- [DOMPurify](https://github.com/cure53/DOMPurify)

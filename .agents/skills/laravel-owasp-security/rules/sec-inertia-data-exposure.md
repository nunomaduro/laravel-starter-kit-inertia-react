---
title: Prevent Inertia.js Data Exposure
impact: HIGH
impactDescription: Prevents sensitive data from being exposed in the data-page HTML attribute on initial load
tags: security, inertia, data-exposure, api-resources, props, react, owasp-r2
---

## Prevent Inertia.js Data Exposure

**Impact: HIGH (Prevents sensitive data from being exposed in the data-page HTML attribute on initial load)**

## Why It Matters

- **Risk**: All Inertia props passed from Laravel controllers are serialized into the `data-page` attribute of the `<div id="app">` element on the initial page load. This HTML is visible to anyone who views page source — even before JavaScript runs
- **Impact**: API keys, secret tokens, admin flags, and other sensitive data embedded in Inertia props are publicly exposed regardless of what the React UI renders or hides
- **OWASP**: React/Inertia R2 — Inertia.js Data Exposure

```html
<!-- Everything in Inertia props is visible here — anyone can View Source -->
<div id="app" data-page="{&quot;component&quot;:&quot;admin/settings/index&quot;,
  &quot;props&quot;:{&quot;toyyibpay&quot;:{&quot;secret_key&quot;:&quot;sk_live_abc123...&quot;}}}">
```

## Incorrect

```php
<?php

// ❌ Full secret key sent to frontend as Inertia prop
class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/settings/index', [
            'toyyibpay' => [
                'secret_key'    => Setting::get('toyyibpay_secret_key'),  // Full key exposed!
                'category_code' => Setting::get('toyyibpay_category_code'),
            ],
        ]);
    }
}
```

```php
<?php

// ❌ Raw model passed as Inertia prop — exposes all database columns
class UserController extends Controller
{
    public function show(User $user): Response
    {
        return Inertia::render('admin/users/show', [
            'user' => $user,  // Includes password_hash, remember_token, 2FA secrets
        ]);
    }
}
```

```php
<?php

// ❌ Shared props expose sensitive fields
class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),  // Includes password, remember_token, all columns
            ],
        ]);
    }
}
```

```php
<?php

// ❌ Admin-only flag passed to non-admin pages — flag is still in data-page HTML
return Inertia::render('student/dashboard', [
    'is_admin'       => auth()->user()->hasRole('admin'),  // Visible in source
    'payment_config' => ['toyyibpay_key' => config('toyyibpay.secret')],  // Exposed!
]);
```

**Problems:**
- Full API keys in Inertia props are embedded in HTML source — no authentication required to read them
- Raw Eloquent models include every column including `password`, `remember_token`, and any sensitive cast fields
- Admin-only props are still in `data-page` HTML even if the React UI never renders them

## Correct

### Mask Secrets — Never Send Full Keys

```php
<?php

declare(strict_types=1);

// ✅ Mask the key — frontend only needs to know if it is configured
class SettingsController extends Controller
{
    public function index(): Response
    {
        $secretKey = (string) Setting::get('toyyibpay_secret_key', '');

        return Inertia::render('admin/settings/index', [
            'toyyibpay' => [
                'secret_key_set'    => $secretKey !== '',
                'secret_key_masked' => $secretKey !== ''
                    ? substr($secretKey, 0, 4) . str_repeat('*', 12)
                    : '',
                'category_code'     => (string) Setting::get('toyyibpay_category_code', ''),
                'sandbox_mode'      => Setting::get('toyyibpay_sandbox_mode', '0') === '1',
                'callback_url'      => route('toyyibpay.callback'),
            ],
        ]);
    }
}
```

### Use ->only() or API Resources — Never Raw Models

```php
<?php

declare(strict_types=1);

// ✅ Only pass fields the UI actually needs
class UserController extends Controller
{
    public function show(User $user): Response
    {
        return Inertia::render('admin/users/show', [
            'user' => $user->only(['id', 'name', 'email', 'status', 'created_at']),
        ]);
    }
}

// ✅ Or use an API Resource for consistent field selection
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'status'     => $this->status,
            'roles'      => $this->roles->pluck('name'),
            'created_at' => $this->created_at->toDateString(),
            // password, remember_token, 2FA fields — NOT included
        ];
    }
}

return Inertia::render('admin/users/show', [
    'user' => new UserResource($user),
]);
```

### Limit Shared Props to What the UI Needs

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                // ✅ Only fields the UI needs — no password, remember_token, or 2FA
                'user' => $request->user() ? [
                    'id'           => $request->user()->id,
                    'name'         => $request->user()->name,
                    'email'        => $request->user()->email,
                    'status'       => $request->user()->status,
                    'current_role' => $request->user()->currentRole?->only(['id', 'name', 'slug']),
                    'roles'        => $request->user()->roles->map->only(['id', 'name', 'slug']),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
```

### Never Pass Secrets or Admin Data to Non-Admin Pages

```php
<?php

declare(strict_types=1);

// ✅ Use middleware to ensure admin data only reaches admin routes
// routes/web.php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index']);
    // Even masked sensitive data only sent to authenticated admin pages
});

// ✅ Student dashboard — no admin flags, no secret keys
Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // Only student-relevant data passed here
});
```

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `$model->only(['id', 'name', ...])` | Limit fields in Inertia props |
| `new UserResource($user)` | Consistent field selection via API Resource |
| `secret_key_masked` + `secret_key_set` | Show masked key — never full value |
| `$request->user()->only([...])` | Shared auth props in HandleInertiaRequests |
| Admin routes in `role:admin` middleware group | Ensure sensitive props never reach non-admin pages |

Reference: [Inertia.js Shared Data](https://inertiajs.com/shared-data) | [Laravel API Resources](https://laravel.com/docs/13.x/eloquent-resources)

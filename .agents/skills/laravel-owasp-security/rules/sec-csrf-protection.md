---
title: Enforce CSRF Protection
impact: HIGH
impactDescription: Prevents forged cross-site requests that perform actions on behalf of authenticated users
tags: security, csrf, inertia, webhooks, middleware, owasp-a08
---

## Enforce CSRF Protection

**Impact: HIGH (Prevents forged cross-site requests that perform actions on behalf of authenticated users)**

## Why It Matters

- **Risk**: A malicious website tricks an authenticated user's browser into submitting a state-changing request (payment, delete, password change) to your application without the user's knowledge
- **Impact**: Unauthorized transactions, account deletion, privilege changes
- **OWASP**: A08:2021 — Software and Data Integrity Failures (includes CSRF)

## Incorrect

```php
<?php

// ❌ Excluding all POST routes from CSRF — disables protection globally
$middleware->validateCsrfTokens(except: ['*']);
```

```php
<?php

// ❌ Excluding authenticated routes from CSRF without justification
$middleware->validateCsrfTokens(except: [
    '/admin/settings',   // Authenticated — should NOT be excluded
    '/payments/*',       // Authenticated — should NOT be excluded
    '/webhooks/*',       // OK — external webhook callback
]);
```

```tsx
// ❌ Custom fetch bypassing Inertia router without CSRF token
async function deleteAccount() {
    await fetch('/account', {
        method: 'DELETE',
        // Missing X-XSRF-TOKEN header — CSRF protection not sent
    });
}
```

```php
<?php

// ❌ Traditional Blade form without @csrf
<form method="POST" action="/profile">
    <input type="text" name="name">
    <button type="submit">Update</button>
    {{-- Missing @csrf — request will be rejected or vulnerable --}}
</form>
```

**Problems:**
- Excluding authenticated routes from CSRF allows cross-site forged requests
- Custom `fetch` calls bypass Inertia's automatic CSRF header injection
- Blade forms without `@csrf` are either rejected (if protection is on) or vulnerable (if excluded)

## Correct

### Only Exclude Stateless Webhook Routes

```php
<?php

// ✅ Only exclude external webhook callbacks that cannot send CSRF tokens
// bootstrap/app.php
$middleware->validateCsrfTokens(except: [
    '/webhooks/toyyibpay',  // Third-party callback — cannot include CSRF token
    '/webhooks/stripe',     // Third-party callback — cannot include CSRF token
]);

// All authenticated routes remain CSRF-protected
```

### Inertia Handles CSRF Automatically

```tsx
import { router, useForm } from '@inertiajs/react';

// ✅ Inertia router automatically sends X-XSRF-TOKEN header
router.post('/profile', { name: 'John' });

// ✅ useForm also handles CSRF automatically
const { data, setData, post } = useForm({ name: '' });
post('/profile');  // X-XSRF-TOKEN sent automatically
```

### Custom fetch Must Include CSRF Token

```tsx
import axios from 'axios';

// ✅ Use axios (Inertia default) — it reads XSRF-TOKEN cookie automatically
await axios.delete('/account');

// ✅ If using native fetch, read the token from the cookie
function getCsrfToken(): string {
    return document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] ?? '';
}

await fetch('/account', {
    method: 'DELETE',
    headers: {
        'X-XSRF-TOKEN': decodeURIComponent(getCsrfToken()),
        'Content-Type': 'application/json',
    },
});
```

### Always Use @csrf in Blade Forms

```blade
{{-- ✅ @csrf in every POST/PUT/DELETE Blade form --}}
<form method="POST" action="/profile">
    @csrf
    @method('PATCH')
    <input type="text" name="name" value="{{ auth()->user()->name }}">
    <button type="submit">Update Profile</button>
</form>
```

### Verify Webhooks with Signature Instead of CSRF

```php
<?php

declare(strict_types=1);

// ✅ Webhook route excluded from CSRF — but verified by signature
class ToyyibPayController extends Controller
{
    public function callback(Request $request): Response
    {
        // Verify authenticity via payment gateway signature/token
        // instead of CSRF (which third parties cannot provide)
        $billCode   = $request->input('billcode');
        $billStatus = $request->input('status_id');

        // Process the verified callback
        $this->processPayment($billCode, $billStatus);

        return response('OK');
    }
}
```

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `validateCsrfTokens(except: ['/webhooks/*'])` | Only exclude third-party callbacks |
| Inertia `router.post/put/delete` | All form submissions — CSRF automatic |
| `useForm` hook | Forms with Inertia — CSRF automatic |
| `axios` for custom HTTP calls | Reads XSRF-TOKEN cookie automatically |
| `@csrf` in Blade forms | Non-Inertia forms |
| Webhook signature verification | Authenticate excluded routes differently |

Reference: [Laravel CSRF Protection](https://laravel.com/docs/13.x/csrf) | [Inertia.js Security](https://inertiajs.com/security)

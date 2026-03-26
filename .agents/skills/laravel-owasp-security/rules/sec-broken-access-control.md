---
title: Prevent Broken Access Control
impact: CRITICAL
impactDescription: Prevents unauthorized access to other users' data and privileged actions
tags: security, access-control, authorization, middleware, owasp-a01
---

## Prevent Broken Access Control

**Impact: CRITICAL (Prevents unauthorized access to other users' data and privileged actions)**

## Why It Matters

- **Risk**: Attackers access other users' records, perform admin actions, or read sensitive data by manipulating IDs or bypassing role checks
- **Impact**: Full data breach, privilege escalation, unauthorized transactions
- **OWASP**: A01:2021 — the most common critical vulnerability across web applications

## Incorrect

```php
<?php

// ❌ No ownership check — any authenticated user can view any payment
class PaymentController extends Controller
{
    public function show(int $id)
    {
        $payment = Payment::find($id);  // No ownership check

        return Inertia::render('payments/show', ['payment' => $payment]);
    }
}
```

```php
<?php

// ❌ Admin route group without role middleware
Route::prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    // Any authenticated user can reach these routes
});
```

```php
<?php

// ❌ Relying on frontend role check without server-side enforcement
class ReportController extends Controller
{
    public function financial()
    {
        // No middleware — assumes React UI hid the link from non-admins
        $data = Payment::all();

        return Inertia::render('reports/financial', compact('data'));
    }
}
```

**Problems:**
- Any authenticated user can access any record by changing the ID in the URL
- Role checks only on the frontend are trivially bypassed — users can call routes directly
- Missing middleware on admin route groups exposes privileged actions to all users

## Correct

### Always Check Ownership

```php
<?php

declare(strict_types=1);

// ✅ Scope resource to authenticated user
class PaymentController extends Controller
{
    public function show(int $id)
    {
        $payment = Payment::where('user_id', auth()->id())
            ->findOrFail($id);

        return Inertia::render('payments/show', ['payment' => $payment]);
    }
}
```

### Protect Route Groups with Middleware

```php
<?php

// ✅ Role middleware enforced at route level
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// ✅ Multiple roles allowed
Route::middleware(['auth', 'role:teacher,moderator,admin'])->prefix('manage')->group(function () {
    Route::resource('classes', ClassManagementController::class);
});
```

### Use Gates and Policies

```php
<?php

declare(strict_types=1);

// ✅ Policy — define authorization logic separately
class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->id === $payment->user_id;
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->id === $payment->user_id
            && $payment->status === 'pending';
    }
}

// ✅ Controller uses Gate/Policy
class PaymentController extends Controller
{
    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        return Inertia::render('payments/show', ['payment' => $payment]);
    }
}
```

### Scope All Queries to the Authenticated User

```php
<?php

declare(strict_types=1);

// ✅ Use global scope or always filter by authenticated user
class RegistrationController extends Controller
{
    public function index()
    {
        $registrations = Registration::where('student_id', auth()->id())
            ->with(['class', 'payments'])
            ->latest()
            ->paginate(20);

        return Inertia::render('student/registrations/index', compact('registrations'));
    }
}
```

### Mirror Server-Side Checks — Never Trust Frontend Alone

```php
<?php

declare(strict_types=1);

// ✅ Middleware enforces access — React UI hiding a link is not security
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/reports/financial', [ReportController::class, 'financial']);
});

// React link may be hidden from non-admins — but the route is still protected
// Even if a user manually navigates to the URL, middleware blocks them
```

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| `->where('user_id', auth()->id())->findOrFail($id)` | Scope any resource to current user |
| `$this->authorize('action', $model)` | Policy-based authorization per action |
| `Route::middleware('role:admin')` | Protect admin route groups |
| `abort_unless($condition, 403)` | Inline authorization guard |
| `Gate::authorize('action', $model)` | Gate-based authorization in services |

Reference: [OWASP Laravel Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html)

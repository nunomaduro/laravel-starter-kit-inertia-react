---
title: Controller Middleware
impact: MEDIUM
impactDescription: Reusable request filtering and modification
tags: controllers, middleware, authentication, authorization
---

## Controller Middleware

**Impact: MEDIUM (Reusable request filtering and modification)**

Use middleware to handle cross-cutting concerns like authentication, rate limiting, and request modification.

## Bad Example

```php
// Authentication and authorization checks in every method
class AdminController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('admin.dashboard');
    }

    public function users()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('admin.users');
    }

    public function settings()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('admin.settings');
    }
}
```

## Good Example

```php
// PHP Attributes (Laravel 13+) — preferred declarative approach
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
#[Middleware('admin')]
class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    #[Middleware('verified')]
    #[Authorize('manage', [Post::class])]
    public function posts()
    {
        return view('admin.posts');
    }

    #[Middleware('subscribed')]
    #[Authorize('create', [Comment::class, 'post'])]
    public function store(Post $post)
    {
        // ...
    }
}
```

```php
// HasMiddleware interface (Laravel 11+) — still supported
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            'admin',
            new Middleware('verified', only: ['store', 'update']),
            new Middleware('throttle:10,1', only: ['store']),
        ];
    }

    public function index()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        return view('admin.users');
    }
}
```

```php
// Using middleware in routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
    Route::resource('admin/users', AdminUserController::class);
});
```

```php
// Custom middleware
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
```

```php
// Register middleware alias in bootstrap/app.php (Laravel 11+)
// Note: The $middleware parameter here is Illuminate\Foundation\Configuration\Middleware,
// not Illuminate\Routing\Controllers\Middleware used above in the controller.
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'subscribed' => EnsureUserIsSubscribed::class,
        ]);
    })
    ->create();
```

```php
// Middleware with parameters
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()?->hasAnyRole($roles)) {
            abort(403);
        }

        return $next($request);
    }
}
```

```php
// Usage with parameters
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('role:admin,manager');

// Middleware for API rate limiting
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

```php
// Conditional middleware using HasMiddleware
class ApiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth:sanctum',
        ];
    }
}
```

```php
// Middleware groups for common patterns
// bootstrap/app.php
$middleware->group('api', [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);
```

## Why

- **DRY**: Authentication/authorization logic in one place
- **Reusability**: Same middleware used across multiple controllers
- **Separation of concerns**: Controllers focus on business logic
- **Composability**: Stack multiple middleware for complex requirements
- **Testability**: Middleware tested independently
- **Declarative**: PHP attributes (Laravel 13+) make middleware and authorization visible at the class/method level
- **Colocated**: `#[Authorize]` keeps policy checks next to the method they protect

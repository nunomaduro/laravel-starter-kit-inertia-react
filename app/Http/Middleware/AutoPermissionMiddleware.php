<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

final class AutoPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Requires the user to have a permission matching the route name for named
     * application routes not in config('permission.route_skip_patterns') and
     * without explicit permission/role middleware.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('permission.route_based_enforcement', false)) {
            return $next($request);
        }

        $route = $request->route();

        if (! $route || ! $route->getName()) {
            return $next($request);
        }

        $routeName = $route->getName();

        if ($this->shouldSkipRoute($routeName)) {
            return $next($request);
        }

        if (! $this->isApplicationRoute($route)) {
            return $next($request);
        }

        if ($this->hasExplicitPermissionMiddleware($route)) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        throw_unless(method_exists($user, 'hasPermissionTo'), UnauthorizedException::missingTraitHasRoles($user));

        if ($user->hasRole('super-admin') || $user->can('bypass-permissions')) {
            return $next($request);
        }

        throw_unless($user->hasPermissionTo($routeName), UnauthorizedException::forPermissions([$routeName]));

        return $next($request);
    }

    private function shouldSkipRoute(string $routeName): bool
    {
        return Str::is(config('permission.route_skip_patterns', []), $routeName);
    }

    private function isApplicationRoute(Route $route): bool
    {
        $action = $route->getAction();

        if (isset($action['controller'])) {
            return str_starts_with((string) $action['controller'], 'App\\');
        }

        if (isset($action['uses']) && $action['uses'] instanceof Closure) {
            return true;
        }

        if (isset($action['file'])) {
            $file = (string) $action['file'];

            return str_contains($file, '/routes/') && ! str_contains($file, '/vendor/');
        }

        return false;
    }

    private function hasExplicitPermissionMiddleware(Route $route): bool
    {
        return collect($route->middleware())
            ->filter(fn ($m) => is_string($m))
            ->contains(fn (string $m) => Str::startsWith($m, ['permission:', 'role:', 'role_or_permission:']));
    }
}

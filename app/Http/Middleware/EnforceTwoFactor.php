<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\AuthSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Enforces two-factor authentication based on the configured policy in AuthSettings.
 *
 *   optional    — no enforcement (default)
 *   admins_only — users with the admin or super-admin role must have 2FA enabled
 *   required    — all authenticated users must have 2FA enabled
 *
 * Users who haven't set up 2FA are redirected to /settings/two-factor.
 * The middleware is skipped for that route to prevent redirect loops.
 */
final class EnforceTwoFactor
{
    /** Routes that must be exempt to avoid redirect loops. */
    private const array EXEMPT_ROUTES = [
        'two-factor.show',
        'login',
        'logout',
        'password.request',
        'password.reset',
        'verification.notice',
        'verification.verify',
        'install',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($this->routeIsExempt($request)) {
            return $next($request);
        }

        try {
            $auth = resolve(AuthSettings::class);
            $enforcement = $auth->two_factor_enforcement;
        } catch (Throwable) {
            return $next($request);
        }

        if ($enforcement === 'optional') {
            return $next($request);
        }

        $needsEnforcement = match ($enforcement) {
            'required' => true,
            'admins_only' => $user->hasRole(['admin', 'super-admin']),
            default => false,
        };

        if ($needsEnforcement && ! $this->userHasTwoFactorEnabled($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Two-factor authentication is required for your account.',
                    'two_factor_required' => true,
                ], 403);
            }

            return to_route('two-factor.show')
                ->with('two_factor_enforcement', true);
        }

        return $next($request);
    }

    private function routeIsExempt(Request $request): bool
    {
        return array_any(self::EXEMPT_ROUTES, fn ($routeName) => $request->routeIs($routeName));
    }

    private function userHasTwoFactorEnabled(\App\Models\User $user): bool
    {
        return $user->hasEnabledTwoFactorAuthentication();
    }
}

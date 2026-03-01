<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limits sensitive 2FA management endpoints (enable, disable, confirm, recovery codes).
 */
final class ThrottleTwoFactorManagement
{
    private const int MAX_ATTEMPTS = 5;

    private const int DECAY_SECONDS = 60;

    /**
     * Paths that require stricter rate limiting (Fortify 2FA management).
     *
     * @var list<string>
     */
    private const array SENSITIVE_PATHS = [
        'user/confirmed-two-factor-authentication',
        'user/two-factor-recovery-codes',
        'user/two-factor-qr-code',
        'user/two-factor-secret-key',
    ];

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        $shouldThrottle = $this->isSensitivePath($request, $path);

        if (! $shouldThrottle) {
            return $next($request);
        }

        $userId = $request->user()?->getKey() ?? $request->session()->getId();
        $key = 'two-factor-management:'.$userId.':'.$path;

        abort_if(RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS), 429, 'Too Many Requests');

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return $next($request);
    }

    private function isSensitivePath(Request $request, string $path): bool
    {
        foreach (self::SENSITIVE_PATHS as $sensitive) {
            if ($path === $sensitive || str_starts_with($path, $sensitive.'/')) {
                return true;
            }
        }

        if ($path === 'user/two-factor-authentication') {
            if ($request->isMethod('POST')) {
                return true;
            }

            return $request->isMethod('DELETE');
        }

        return false;
    }
}

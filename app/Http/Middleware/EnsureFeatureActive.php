<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\FeatureHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abort with 404 when the given feature is inactive for the authenticated user.
 * Guests are allowed through (no user = no feature check).
 * Respects globally disabled modules (always off for everyone).
 */
final class EnsureFeatureActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        abort_unless(FeatureHelper::isActiveForKey($featureKey, $user), 404);

        return $next($request);
    }
}

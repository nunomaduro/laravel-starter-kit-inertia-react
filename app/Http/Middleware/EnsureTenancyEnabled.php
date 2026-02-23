<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect to dashboard when tenancy (multi-organization mode) is disabled.
 * Apply to organization routes so single-tenant deployments hide org management UI.
 */
final class EnsureTenancyEnabled
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('tenancy.enabled', true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Multi-organization mode is not enabled.'),
                    'error' => 'tenancy_disabled',
                ], 403);
            }

            return to_route('dashboard');
        }

        return $next($request);
    }
}

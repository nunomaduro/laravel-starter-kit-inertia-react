<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure an active tenant (organization) context exists. Abort 403 if not.
 */
final class EnsureTenantContext
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! TenantContext::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Organization context is required.',
                    'error' => 'missing_organization_context',
                ], 403);
            }

            return to_route('dashboard')->with('flash', [
                'type' => 'warning',
                'message' => 'Please select an organization to access this page.',
            ]);
        }

        return $next($request);
    }
}

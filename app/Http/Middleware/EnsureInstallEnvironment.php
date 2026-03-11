<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only allow the web installer and express install when APP_ENV is local (or testing).
 * Returns 404 in production/staging to avoid exposing install routes.
 */
final class EnsureInstallEnvironment
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment(['local', 'testing'])) {
            abort(404);
        }

        return $next($request);
    }
}

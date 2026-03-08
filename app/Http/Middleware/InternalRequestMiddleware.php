<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InternalRequestMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('internal.allowed_ips', ['127.0.0.1', '::1']);

        abort_unless(in_array($request->ip(), (array) $allowedIps, true), 403, 'Forbidden');

        return $next($request);
    }
}

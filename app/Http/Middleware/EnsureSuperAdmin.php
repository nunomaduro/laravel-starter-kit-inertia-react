<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to the current panel to super-admins only.
 * Use on the System panel; non-super-admins get 403.
 */
final class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if(! $user || ! $user->isSuperAdmin(), 403, __('Only super-admins can access this area.'));

        return $next($request);
    }
}

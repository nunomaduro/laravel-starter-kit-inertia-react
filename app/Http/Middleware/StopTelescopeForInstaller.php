<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Telescope\Telescope;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops Telescope from recording anything for this request.
 * Used on /install so Telescope never touches the DB before the app is installed.
 */
final class StopTelescopeForInstaller
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install') || $request->is('install/*')) {
            Telescope::stopRecording();
            // Use cookie session so CSRF and step state persist without DB
            config(['session.driver' => 'cookie']);
        }

        return $next($request);
    }
}

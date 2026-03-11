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
            // Disable Telescope entirely — stopRecording() prevents new entries but
            // Telescope still tries to flush queued entries in storeEntriesBeforeTermination(),
            // which throws a PDO exception when telescope_entries table doesn't exist yet.
            config(['telescope.enabled' => false]);
            Telescope::stopRecording();
            // Use cookie session so CSRF and step state persist without DB.
            config(['session.driver' => 'cookie']);
            // Use array cache so Governor's ParseCustomPolicyActions middleware (registered
            // globally via pushMiddleware) does not query the "cache" table, which may not
            // exist yet on a fresh database connection during the install flow.
            config(['cache.default' => 'array']);
        }

        return $next($request);
    }
}

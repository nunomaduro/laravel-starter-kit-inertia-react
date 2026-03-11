<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\SetupWizardSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * When installation is not finished, redirect to the install page.
 * Only runs in local/testing (where install routes exist). Skips install and health routes.
 */
final class RedirectToInstallerIfNotSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        if (str_starts_with($request->path(), 'install')) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), ['up', 'up.ready'], true)) {
            return $next($request);
        }

        try {
            $wizard = resolve(SetupWizardSettings::class);
            if ($wizard->setup_completed) {
                return $next($request);
            }
        } catch (Throwable) {
            // Settings/DB not available — show installer
        }

        return to_route('install');
    }
}

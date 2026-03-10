<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\SetupWizardSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Redirects to /admin when setup is already complete, preventing re-installation
 * through the web installer.
 */
final class EnsureNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $wizard = resolve(SetupWizardSettings::class);

            if ($wizard->setup_completed) {
                return redirect('/admin');
            }
        } catch (Throwable) {
            // Settings table not yet available — allow access to installer
        }

        return $next($request);
    }
}

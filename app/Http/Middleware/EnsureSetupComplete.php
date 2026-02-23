<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\SetupWizardSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Redirects super-admins to the setup wizard when initial setup hasn't been completed.
 * Scoped to the Filament admin panel via AdminPanelProvider middleware.
 */
final class EnsureSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        if (! $request->user()->isSuperAdmin()) {
            return $next($request);
        }

        try {
            $settings = resolve(SetupWizardSettings::class);

            if ($settings->setup_completed) {
                return $next($request);
            }
        } catch (Throwable) {
            // Settings table may not exist yet (fresh install, migrations pending).
            return $next($request);
        }

        // Don't redirect if already on the wizard page or logging out.
        $currentRoute = $request->route()?->getName();
        $excludedRoutes = [
            'filament.admin.pages.setup-wizard',
            'filament.admin.auth.logout',
            'logout',
        ];

        if (in_array($currentRoute, $excludedRoutes, true)) {
            return $next($request);
        }

        return to_route('filament.admin.pages.setup-wizard');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\SetupWizardSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Redirects super-admins to the Setup Wizard when initial setup has not been completed.
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

        // Don't redirect if already on the App settings page, setup wizard, or logging out.
        $currentRoute = $request->route()?->getName();
        $excludedRoutes = [
            'filament.admin.pages.manage-app',
            'filament.admin.pages.setup-wizard',
            'filament.admin.auth.logout',
            'filament.system.pages.manage-app',
            'filament.system.pages.setup-wizard',
            'filament.system.auth.logout',
            'logout',
        ];

        if (in_array($currentRoute, $excludedRoutes, true)) {
            return $next($request);
        }

        return redirect()->to(route('filament.system.pages.setup-wizard'));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Features\RegistrationFeature;
use App\Settings\AuthSettings;
use App\Support\FeatureHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect guests to login when registration is disabled.
 *
 * Checks both the Pennant RegistrationFeature flag and the AuthSettings toggle.
 * Both must be enabled for registration to be accessible.
 * Respects globally disabled modules.
 */
final class EnsureRegistrationEnabled
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pennantActive = FeatureHelper::isActiveForClass(RegistrationFeature::class, $request->user());
        $settingsEnabled = resolve(AuthSettings::class)->registration_enabled;

        if ($pennantActive && $settingsEnabled) {
            return $next($request);
        }

        return to_route('login')
            ->with('message', __('Registration is currently disabled.'));
    }
}

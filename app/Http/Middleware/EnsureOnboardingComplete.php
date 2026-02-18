<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Features\OnboardingFeature;
use App\Models\User;
use App\Support\FeatureHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects authenticated users who have not completed onboarding to the onboarding page.
 * When OnboardingFeature is inactive for the user, onboarding is skipped (no redirect).
 */
final class EnsureOnboardingComplete
{
    /**
     * Route names that are allowed without completing onboarding.
     *
     * @var list<string>
     */
    private const array EXCLUDED_ROUTES = [
        'onboarding',
        'onboarding.store',
        'logout',
        'password.confirm',
        'password.confirm.store',
        'verification.notice',
        'verification.verify',
        'verification.send',
    ];

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        /** @var User $user */
        $user = $request->user();

        if (! FeatureHelper::isActiveForClass(OnboardingFeature::class, $user)) {
            return $next($request);
        }

        if ($user->onboarding_completed) {
            return $next($request);
        }

        if (! $user->hasVerifiedEmail()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, self::EXCLUDED_ROUTES, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return $next($request);
        }

        if ($request->is('admin/*') || $request->is('filament/*')) {
            return $next($request);
        }

        return to_route('onboarding');
    }
}

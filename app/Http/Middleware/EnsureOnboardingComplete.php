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
 * Redirects authenticated users who have not completed onboarding to the next unfinished step.
 * Uses spatie/laravel-onboard; when OnboardingFeature is inactive, onboarding is skipped.
 */
final class EnsureOnboardingComplete
{
    /**
     * Route names that are allowed without completing onboarding (step targets and auth).
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
        'user-profile.edit',
        'user-profile.update',
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

        if (! $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($user->onboarding_completed) {
            return $next($request);
        }

        $onboarding = $user->onboarding();

        if (! $onboarding->inProgress()) {
            if ($onboarding->finished()) {
                $user->update(['onboarding_completed' => true]);
            }

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

        $nextStep = $onboarding->nextUnfinishedStep();

        return $nextStep !== null
            ? redirect()->to($nextStep->link)
            : to_route('onboarding');
    }
}

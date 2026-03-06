<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\GetRequiredTermsVersionsForUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects authenticated users who have not accepted all required terms versions
 * to the terms acceptance page until they accept.
 */
final readonly class EnsureTermsAccepted
{
    /**
     * Route names that are always allowed without terms acceptance.
     *
     * @var list<string>
     */
    private const array EXCLUDED_ROUTES = [
        'terms.accept',
        'terms.accept.store',
        'legal.terms',
        'legal.privacy',
        'logout',
        'password.confirm',
        'password.edit',
        'password.update',
        'password.request',
        'password.email',
        'password.reset',
        'password.store',
        'verification.notice',
        'verification.verify',
        'verification.send',
    ];

    public function __construct(
        private GetRequiredTermsVersionsForUser $getRequiredTermsVersionsForUser
    ) {}

    /**
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        /** @var User $user */
        $user = $request->user();

        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, self::EXCLUDED_ROUTES, true)) {
            return $next($request);
        }

        if ($request->is('terms/accept') || $request->is('legal/terms') || $request->is('legal/privacy')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return $next($request);
        }

        if ($request->is('admin/*') || $request->is('filament/*')) {
            return $next($request);
        }

        $pending = $this->getRequiredTermsVersionsForUser->handle($user);
        if ($pending->isEmpty()) {
            return $next($request);
        }

        $acceptUrl = url('/terms/accept', ['intended' => $request->fullUrl()]);

        return redirect()->to($acceptUrl)->with('terms_required', true);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Settings\SeoSettings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Pennant\Feature;
use Spatie\Honeypot\Honeypot;
use Throwable;

final class HandleInertiaRequests extends Middleware
{
    /**
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $quote = Inspiring::quotes()->random();
        assert(is_string($quote));

        [$message, $author] = str($quote)->explode('-');

        $user = $request->user();

        $honeypot = resolve(Honeypot::class);

        $features = [];
        foreach (config('feature-flags.inertia_features', []) as $name => $featureClass) {
            $features[$name] = $user
                ? Feature::for($user)->active($featureClass)
                : (new $featureClass)->defaultValue;
        }

        $tenancyEnabled = config('tenancy.enabled', true);
        $currentOrganization = $user ? \App\Services\TenantContext::get() : null;
        $userOrganizations = $user
            ? $user->organizations()->orderBy('name')->get(['id', 'name', 'slug'])
            : [];

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => mb_trim((string) $message), 'author' => mb_trim((string) $author)],
            'auth' => [
                'user' => $user,
                'permissions' => $user?->getAllPermissions()->pluck('name')->all() ?? [],
                'roles' => $user?->getRoleNames()->all() ?? [],
                'can_bypass' => $user?->can('bypass-permissions') ?? false,
                'tenancy_enabled' => $tenancyEnabled,
                'current_organization' => $currentOrganization?->only(['id', 'name', 'slug']),
                'organizations' => $tenancyEnabled ? $userOrganizations : [],
            ],
            'features' => $features,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'honeypot' => $honeypot->enabled() ? $honeypot->toArray() : null,
            'cookieConsent' => config('cookie-consent.enabled', true) ? [
                'accepted' => $request->hasCookie(config('cookie-consent.cookie_name', 'laravel_cookie_consent')),
                'cookieName' => config('cookie-consent.cookie_name', 'laravel_cookie_consent'),
                'lifetimeDays' => (int) config('cookie-consent.cookie_lifetime', 365 * 20),
            ] : null,
            'seo' => $this->seoSharedData(),
        ];
    }

    /**
     * @return array{meta_title: string, meta_description: string, og_image: string|null, app_url: string}
     */
    private function seoSharedData(): array
    {
        try {
            $settings = resolve(SeoSettings::class);

            return [
                'meta_title' => $settings->meta_title ?: config('app.name'),
                'meta_description' => $settings->meta_description ?? '',
                'og_image' => $settings->og_image,
                'app_url' => mb_rtrim(config('app.url'), '/'),
            ];
        } catch (Throwable) {
            return [
                'meta_title' => config('app.name'),
                'meta_description' => '',
                'og_image' => null,
                'app_url' => mb_rtrim(config('app.url'), '/'),
            ];
        }
    }
}

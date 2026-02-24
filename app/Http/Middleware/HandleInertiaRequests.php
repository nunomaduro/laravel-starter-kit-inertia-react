<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use App\Settings\SeoSettings;
use App\Support\FeatureHelper;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Inertia\Middleware;
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
        $theme = $this->resolveTheme();
        View::share('theme', $theme);

        $quote = Inspiring::quotes()->random();
        assert(is_string($quote));

        [$message, $author] = str($quote)->explode('-');

        $user = $request->user();

        $honeypot = resolve(Honeypot::class);

        $features = [];
        foreach (config('feature-flags.inertia_features', []) as $name => $featureClass) {
            $features[$name] = FeatureHelper::isActiveForKey($name, $user);
        }

        $tenancyEnabled = config('tenancy.enabled', true);
        $currentOrganization = $user ? TenantContext::get() : null;
        $userOrganizations = $user
            ? $user->organizations()->orderBy('name')->get(['id', 'name', 'slug'])
            : [];

        return [
            ...parent::share($request),
            'flash' => fn () => $request->session()->get('flash'),
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
            'theme' => $theme,
            'branding' => $this->resolveBranding(...),
        ];
    }

    /**
     * Resolve theme from DB (settings table) so Manage Theme changes take effect immediately.
     * Bypasses Settings class cache by reading directly. Falls back to config when unavailable.
     *
     * @return array{preset: string, base_color: string, radius: string, font: string, default_appearance: string}
     */
    private function resolveTheme(): array
    {
        $defaults = [
            'preset' => config('theme.preset', 'default'),
            'base_color' => config('theme.base_color', 'neutral'),
            'radius' => config('theme.radius', 'default'),
            'font' => config('theme.font', 'instrument-sans'),
            'default_appearance' => config('theme.default_appearance', 'system'),
        ];

        try {
            $rows = DB::table('settings')->where('group', 'theme')->get(['name', 'payload']);
            if ($rows->isEmpty()) {
                return $defaults;
            }
            $db = [];
            foreach ($rows as $row) {
                $db[$row->name] = is_string($row->payload) ? json_decode($row->payload, true) : $row->payload;
            }

            return [
                'preset' => $db['preset'] ?? $defaults['preset'],
                'base_color' => $db['base_color'] ?? $defaults['base_color'],
                'radius' => $db['radius'] ?? $defaults['radius'],
                'font' => $db['font'] ?? $defaults['font'],
                'default_appearance' => $db['default_appearance'] ?? $defaults['default_appearance'],
            ];
        } catch (Throwable) {
            return $defaults;
        }
    }

    /**
     * Resolve branding at response time (after SetTenantContext / ApplyOrganizationSettings).
     *
     * @return array{logoUrl: string|null, themePreset: string|null, themeRadius: string|null, themeFont: string|null, allowUserCustomization: bool}
     */
    private function resolveBranding(): array
    {
        $organization = TenantContext::get();

        if (! $organization instanceof \App\Models\Organization) {
            return [
                'logoUrl' => null,
                'themePreset' => null,
                'themeRadius' => null,
                'themeFont' => null,
                'allowUserCustomization' => false,
            ];
        }

        return resolve(OrganizationSettingsService::class)->getBranding($organization);
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

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\Organization;
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

use function getPermissionsTeamId;
use function setPermissionsTeamId;

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
        $theme = $this->resolveTheme($request);
        View::share('theme', $theme);

        $quote = Inspiring::quotes()->random();
        assert(is_string($quote));

        [$message, $author] = str($quote)->explode('-');

        $user = $request->user();

        $honeypot = resolve(Honeypot::class);

        [$authPayload, $features] = $this->resolveAuthAndFeaturesForInertia($user);

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
            'auth' => array_merge($authPayload, [
                'tenancy_enabled' => $tenancyEnabled,
                'current_organization' => $currentOrganization?->only(['id', 'name', 'slug']),
                'organizations' => $tenancyEnabled ? $userOrganizations : [],
            ]),
            'features' => $features,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'honeypot' => $honeypot->enabled() ? $honeypot->toArray() : null,
            'cookieConsent' => config('cookie-consent.enabled', true) ? [
                'accepted' => $request->hasCookie(config('cookie-consent.cookie_name', 'laravel_cookie_consent')),
                'cookieName' => config('cookie-consent.cookie_name', 'laravel_cookie_consent'),
                'lifetimeDays' => (int) config('cookie-consent.cookie_lifetime', 365 * 20),
            ] : null,
            'seo' => $this->seoSharedData(),
            'setup_complete' => $this->resolveSetupState(...),
            'theme' => $theme,
            'branding' => $this->resolveBranding(...),
            'notifications' => [
                'unread_count' => $user?->unreadNotifications()->count() ?? 0,
            ],
            'announcements' => $this->resolveAnnouncements($user),
        ];
    }

    /**
     * Build auth (roles/permissions/can_bypass) and shared features for Inertia.
     *
     * Spatie teams scope getRoleNames()/getAllPermissions() to the current team. Super-admin
     * and bypass-permissions live in team 0; when TenantContext has an org, the resolver
     * returns that org id and global roles disappear. We therefore resolve with team id 0
     * when the user has super-admin globally so the sidebar and useCan see full access.
     * For super-admins we also force all inertia_features to true so org overrides do not
     * hide modules in the UI.
     *
     * @return array{0: array{user: mixed, permissions: array<string>, roles: array<string>, can_bypass: bool}, 1: array<string, bool>}
     */
    private function resolveAuthAndFeaturesForInertia(mixed $user): array
    {
        if (! $user) {
            $features = [];
            foreach (array_keys(config('feature-flags.inertia_features', [])) as $name) {
                $features[$name] = false;
            }

            return [
                [
                    'user' => null,
                    'permissions' => [],
                    'roles' => [],
                    'can_bypass' => false,
                ],
                $features,
            ];
        }

        // Detect super-admin in global team 0 only (org context would hide it otherwise).
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId(0);
        try {
            $isSuperAdmin = $user->hasRole('super-admin');
        } finally {
            setPermissionsTeamId($previousTeamId);
        }

        if ($isSuperAdmin) {
            setPermissionsTeamId(0);
            try {
                $canBypass = true;
                $permissions = $user->getAllPermissions()->pluck('name')->all();
                $roles = $user->getRoleNames()->all();
            } finally {
                setPermissionsTeamId($previousTeamId);
            }
            $features = [];
            foreach (array_keys(config('feature-flags.inertia_features', [])) as $name) {
                $features[$name] = true;
            }
        } else {
            $permissions = $user->getAllPermissions()->pluck('name')->all();
            $roles = $user->getRoleNames()->all();
            $canBypass = $user->can('bypass-permissions') || $user->hasRole('super-admin');
            $features = [];
            foreach (config('feature-flags.inertia_features', []) as $name => $featureClass) {
                $features[$name] = FeatureHelper::isActiveForKey($name, $user);
            }
        }

        return [
            [
                'user' => $user,
                'permissions' => $permissions,
                'roles' => $roles,
                'can_bypass' => $canBypass,
            ],
            $features,
        ];
    }

    /**
     * Resolve theme from DB (settings table) so Manage Theme changes take effect immediately.
     * Bypasses Settings class cache by reading directly. Falls back to config when unavailable.
     *
     * @return array{preset: string, base_color: string, radius: string, font: string, default_appearance: string, dark: string, primary: string, light: string, skin: string, layout: string, menuColor: string, menuAccent: string, canCustomize: bool, allowUserThemeCustomization: bool, userMode: string, lockedSettings: string[]}
     */
    private function resolveTheme(Request $request): array
    {
        $defaults = [
            'preset' => config('theme.preset', 'default'),
            'base_color' => config('theme.base_color', 'neutral'),
            'radius' => config('theme.radius', 'default'),
            'font' => config('theme.font', 'instrument-sans'),
            'default_appearance' => config('theme.default_appearance', 'system'),
            'dark' => '',
            'primary' => '',
            'light' => '',
            'skin' => 'shadow',
            'layout' => 'main',
            'menuColor' => 'default',
            'menuAccent' => 'subtle',
            'canCustomize' => false,
            'userMode' => 'system',
        ];

        $user = $request->user();

        try {
            $rows = DB::table('settings')->where('group', 'theme')->get(['name', 'payload']);

            $db = [];
            foreach ($rows as $row) {
                $db[$row->name] = is_string($row->payload) ? json_decode($row->payload, true) : $row->payload;
            }

            // Org overrides take precedence over global settings.
            $organization = TenantContext::get();
            if ($organization instanceof Organization) {
                $orgRows = DB::table('organization_settings')
                    ->where('organization_id', $organization->id)
                    ->where('group', 'theme')
                    ->get(['name', 'payload']);

                foreach ($orgRows as $orgRow) {
                    $db[$orgRow->name] = json_decode((string) $orgRow->payload, true);
                }
            }

            $allowUserCustomization = (bool) ($db['allow_user_theme_customization'] ?? true);
            $isOrgAdmin = $user !== null && $organization instanceof Organization && (
                $user->isOrganizationAdmin()
                || $user->canInOrganization('org.settings.manage', $organization)
            );
            $isAdmin = $user !== null && ($isOrgAdmin || $user->isSuperAdmin());

            // Load org-level branding user controls
            $orgBranding = [];
            if ($organization instanceof Organization) {
                $brandingRows = DB::table('organization_settings')
                    ->where('organization_id', $organization->id)
                    ->where('group', 'branding')
                    ->whereIn('name', ['user_can_change_colors', 'user_can_change_font', 'user_can_change_layout', 'user_can_change_logo'])
                    ->get(['name', 'payload']);
                foreach ($brandingRows as $row) {
                    $orgBranding[$row->name] = json_decode((string) $row->payload, true);
                }
            }

            $canCustomize = $user !== null && ($isAdmin || $allowUserCustomization);

            $userMode = 'system';
            if ($user !== null) {
                try {
                    $userMode = $user->theme_mode ?? 'system';
                } catch (Throwable) {
                    $userMode = 'system';
                }
            }

            return [
                'preset' => $db['preset'] ?? $defaults['preset'],
                'base_color' => $db['base_color'] ?? $defaults['base_color'],
                'radius' => $db['border_radius'] ?? $db['radius'] ?? $defaults['radius'],
                'font' => $db['font'] ?? $defaults['font'],
                'default_appearance' => $db['default_appearance'] ?? $defaults['default_appearance'],
                'dark' => $db['dark_color_scheme'] ?? $defaults['dark'],
                'primary' => $db['primary_color'] ?? $defaults['primary'],
                'light' => $db['light_color_scheme'] ?? $defaults['light'],
                'skin' => $db['card_skin'] ?? $defaults['skin'],
                'layout' => $db['sidebar_layout'] ?? $defaults['layout'],
                'menuColor' => $db['menu_color'] ?? $defaults['menuColor'],
                'menuAccent' => $db['menu_accent'] ?? $defaults['menuAccent'],
                'canCustomize' => $canCustomize,
                'canCustomizeGranular' => [
                    'colors' => $isAdmin || ($allowUserCustomization && (bool) ($orgBranding['user_can_change_colors'] ?? true)),
                    'font' => $isAdmin || ($allowUserCustomization && (bool) ($orgBranding['user_can_change_font'] ?? true)),
                    'layout' => $isAdmin || ($allowUserCustomization && (bool) ($orgBranding['user_can_change_layout'] ?? true)),
                    'logo' => $organization instanceof Organization && $user !== null && (
                        $isAdmin
                        || (($db['allow_user_logo_upload'] ?? false) && $allowUserCustomization && (bool) ($orgBranding['user_can_change_logo'] ?? false))
                    ),
                ],
                'allowUserThemeCustomization' => $allowUserCustomization,
                'userMode' => $userMode,
                'lockedSettings' => $db['locked_settings'] ?? [],
                'canManageBranding' => $organization instanceof Organization && $user !== null && (
                    $user->isOrganizationAdmin()
                    || $user->canInOrganization('org.settings.manage', $organization)
                    || (
                        ($db['allow_user_logo_upload'] ?? false)
                        && ($db['allow_user_theme_customization'] ?? true)
                    )
                ),
            ];
        } catch (Throwable) {
            return $defaults;
        }
    }

    private function resolveSetupState(): bool
    {
        try {
            return resolve(\App\Settings\SetupWizardSettings::class)->setup_completed;
        } catch (Throwable) {
            return true; // Fail open when settings table unavailable (e.g. fresh install)
        }
    }

    /**
     * Resolve branding at response time (after SetTenantContext / ApplyOrganizationSettings).
     *
     * @return array{logoUrl: string|null, themePreset: string|null, themeRadius: string|null, themeFont: string|null, allowUserCustomization: bool}
     */
    /**
     * Active announcements for the current user: global + current org, within start/end and active.
     *
     * @return array<int, array{id: int, title: string, body: string, level: string}>
     */
    private function resolveAnnouncements($user): array
    {
        if ($user === null) {
            return [];
        }

        $tenantId = TenantContext::id();

        $query = Announcement::query()
            ->active()
            ->where(function ($q) use ($tenantId): void {
                $q->whereNull('organization_id');
                if ($tenantId !== null) {
                    $q->orWhere('organization_id', $tenantId);
                }
            })
            ->orderByRaw('CASE WHEN organization_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('position')
            ->orderBy('created_at', 'desc');

        return $query->get(['id', 'title', 'body', 'level'])
            ->map(fn (Announcement $a): array => [
                'id' => $a->id,
                'title' => $a->title,
                'body' => $a->body,
                'level' => $a->level->value,
            ])
            ->values()
            ->all();
    }

    private function resolveBranding(): array
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return [
                'logoUrl' => null,
                'logoUrlDark' => null,
                'themePreset' => null,
                'themeRadius' => null,
                'themeFont' => null,
                'allowUserCustomization' => true,
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

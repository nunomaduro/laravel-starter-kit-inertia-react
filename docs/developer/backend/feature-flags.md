# Feature Flags

## Purpose

Feature flags are provided by **Laravel Pennant** (`laravel/pennant`) with class-based features. The admin UI for managing flags and segments is **stephenjude/filament-feature-flags**, registered in the Filament admin panel. Resolved flags are exposed to the Inertia frontend via shared props. Routes, Filament resources, and impersonation are gated by the same features where applicable.

## Class-based features

- **Location**: `App\Features\*`
- **Trait**: Use `Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver` so the Filament plugin can discover and manage the feature.
- **Default**: Set `public bool $defaultValue = true` (or `false`) on the class. When there is no segment override, this value is used. In this app, `config('filament-feature-flags.default')` is `true`, so all features are visible after setup; super-admin can turn them off per segment (e.g. role, user) from Filament → Settings → **Manage Features & Segments**.

Example:

```php
namespace App\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class ExampleFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
```

## Config: feature-flags.php

- **`globally_disabled`**: List of feature keys that are always off for everyone (including super-admins). Sourced from env `GLOBALLY_DISABLED_MODULES` (comma-separated, e.g. `blog,changelog,gamification`). Checked before Pennant; when a feature is globally disabled, Pennant is not consulted.
- **`inertia_features`**: `'key' => FeatureClass` — each key is exposed to the frontend as `features.key` (boolean). Used by `HandleInertiaRequests` for shared props.
- **`route_feature_map`**: `'key' => FeatureClass` — keys are used by the `feature` route middleware (e.g. `feature:blog`). Only keys listed here can be used in middleware.

## Features in this app

| Key | Gates |
|-----|-------|
| `example` | Demo/template feature (no routes); in `inertia_features` only |
| `blog` | Blog routes, sidebar item, Filament Post resource |
| `changelog` | Changelog route, sidebar, Filament Changelog Entry resource |
| `help` | Help routes, sidebar, Filament Help Article resource |
| `contact` | Contact form routes, welcome nav, Filament Contact Submission resource |
| `cookie_consent` | Cookie consent banner (frontend), cookie-consent accept route |
| `profile_pdf_export` | Dashboard “Export profile (PDF)” button, profile export-pdf route |
| `onboarding` | Onboarding redirect (when off, onboarding is skipped) |
| `two_factor_auth` | Two-factor settings route, settings nav “Two-Factor Auth” |
| `impersonation` | `User::canImpersonate()` (in addition to super-admin role) |
| `personal_data_export` | Settings “Data export” + personal-data-export routes |
| `registration` | Register link on welcome; backend uses AuthSettings + `registration.enabled` middleware |
| `api_access` | Sanctum API routes under `/api/v1/*` |
| `scramble_api_docs` | `/docs/api` and `/docs/api.json` (middleware: `EnsureScrambleApiDocsVisible`) |
| `appearance_settings` | Settings “Appearance” link, appearance edit route |
| `gamification` | XP, levels, achievements (cjmellor/level-up); signup XP, Profile Completed; settings "Level & achievements"; Filament UserLevelWidget |

## Resetting to defaults

If features were previously stored (e.g. by Pennant or by segments) and you want every user to see all features again, run:

```bash
php artisan features:reset-to-defaults
```

This purges Pennant’s stored feature values and clears all feature segments. Features then re-resolve to their class default (on). You can then adjust per user or segment from Filament → Settings → **Manage Features & Segments**.

## Route middleware

- **`feature:{key}`** — `App\Http\Middleware\EnsureFeatureActive`. For authenticated users, if the feature is inactive, aborts with 404; guests are allowed through. Keys must exist in `config/feature-flags.php` `route_feature_map`.
- **`registration.enabled`** — `App\Http\Middleware\EnsureRegistrationEnabled`. Redirects to login with a message when `AuthSettings::registration_enabled` is false. Applied to register and register.store.

Applied in `routes/web.php` and `routes/api.php` (e.g. `feature:blog` on blog group, `feature:api_access` on API sanctum group). Scramble docs are gated by `EnsureScrambleApiDocsVisible` in the web middleware stack (path `docs/api`).

## Global module disable

To turn off a feature for everyone (including super-admins) at the system level, add it to `GLOBALLY_DISABLED_MODULES` in `.env` (comma-separated keys, e.g. `blog,changelog,gamification`). The `FeatureHelper` checks this list before Pennant; globally disabled features are always off. Use for deployments where certain modules should not be available in a given environment.

## FeatureHelper

`App\Support\FeatureHelper` centralizes feature checks with global-disable support:

- `FeatureHelper::isActiveForKey(string $key, ?User $user = null): bool` — check by feature key (e.g. `blog`).
- `FeatureHelper::isActiveForClass(string $featureClass, ?User $user = null): bool` — check by feature class (e.g. `BlogFeature::class`).
- `FeatureHelper::isGloballyDisabled(string $key): bool` — check if a feature key is in the global disable list.

Use these instead of `Feature::for($user)->active($featureClass)` when you need global-disable behavior.

## Filament resource gating

PostResource, ChangelogEntryResource, HelpArticleResource, and ContactSubmissionResource override `canAccess()` to require the corresponding feature (Blog, Changelog, Help, Contact) to be active for the current user. When the feature is off (including when globally disabled), the resource and its nav item are hidden and access returns 403.

## Impersonation

`User::canImpersonate()` returns true only when the user has the `super-admin` role **and** the Impersonation feature is active for that user. Globally disabled impersonation turns this off for everyone.

## Exposing to Inertia

- **Middleware**: `HandleInertiaRequests` builds a `features` array from `config('feature-flags.inertia_features')`. For each entry it uses `FeatureHelper::isActiveForKey()` so globally disabled features are always `false`.
- **Guests**: Each feature uses the class’s `$defaultValue` (not an empty object).

Frontend usage:

```ts
const { features } = usePage<SharedData>().props;
if (features?.blog) {
  // show blog nav / content
}
```

Types: `resources/js/types/index.d.ts` defines `SharedFeatures` and `SharedData.features`. Sidebar and welcome page filter nav items by `features[item.feature]`; settings layout filters by feature for 2FA, Appearance, and Data export.

## Admin UI

- **Plugin**: `Stephenjude\FilamentFeatureFlag\FeatureFlagPlugin` in `AdminPanelProvider`.
- **Nav**: Settings → “Manage Features” (or label from `config/filament-feature-flags.php`).
- **Config**: `config/filament-feature-flags.php` — default scope `App\Models\User`, segments (e.g. by email), panel group/label/icon.

Admins can turn a feature on for everyone, or define segments to enable for a subset of users.

## Pennant config

- **Config**: `config/pennant.php`
- **Store**: `PENNANT_STORE` — `database` (default) or `array`. Database uses the `features` table (Pennant migration).

## Adding a new feature

1. Create a class in `App\Features\*` with `WithFeatureResolver` and optional `$defaultValue`.
2. Add it to `config/feature-flags.php` under `inertia_features` if the frontend needs it.
3. Add it to `route_feature_map` if you will use `feature:key` middleware on routes.
4. Optionally gate a Filament resource with `canAccess()` and the same feature class.
5. Run migrations if Pennant is using database; the Filament plugin will list the new feature after discovery.

## Reference

- Compare with boilerplate (including gamification): `compare_features.md`.

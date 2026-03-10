# Database-backed settings

## Purpose

Application settings are stored in the database using **spatie/laravel-settings** and edited in the Filament admin panel via **filament/spatie-laravel-settings-plugin**. Settings are strongly typed, optionally cached, and support per-organization overrides for multi-tenant scenarios.

## Architecture: Dual-Layer Resolution

```
Per-request (middleware):
  organization_settings table (org-specific sparse overrides)
    → Spatie settings table (global defaults, typed PHP classes)
      → config/*.php (env fallbacks, immutable floor)
```

1. **Global overlay** — `SettingsOverlayServiceProvider::boot()` writes every Spatie settings value into `config()` at application boot. All existing `config('...')` consumers transparently read DB-backed values.
2. **Per-org overlay** — `ApplyOrganizationSettings` middleware runs after `SetTenantContext`. It loads the current organization's overrides from the `organization_settings` table (cached 60 min) and writes them into `config()`, replacing global values for that request.

### What stays in `.env` (never moves to DB)

Infrastructure needed before the DB is available: `APP_KEY`, `APP_ENV`, `APP_DEBUG`, `APP_URL`, `DB_*`, `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `BROADCAST_CONNECTION`, `REDIS_*`, `TYPESENSE_*`, `VITE_*`.

`LOG_CHANNEL` and `LOG_LEVEL` can now also be managed via `LoggingSettings` (see table below), but the overlay applies **after** the logger is bootstrapped — meaning a cache clear + process restart is required for changes to take effect.

## Settings classes

- **Location**: `App\Settings\*`
- **Groups**: Each class defines `group()` (e.g. `app`, `auth`, `seo`). Properties are stored under that group in the `settings` table.
- **Registration**: Settings in `app/Settings` are auto-discovered (see `config/settings.php` → `auto_discover_settings`).
- **Encryption**: Classes that hold secrets define `public static function encrypted(): array` returning property names. Spatie encrypts these at rest using `APP_KEY`.

### All settings groups (29)

| Class | Group | Config Target | Org-Overridable | Filament Page |
|-------|-------|---------------|:---:|---|
| `AppSettings` | app | `app.*` | No | Settings > App |
| `AuthSettings` | auth | `services.google.*`, `services.github.*`, `session.lifetime` | No | Settings > Auth |
| `SeoSettings` | seo | `seo.*` | No | Settings > SEO |
| `LoggingSettings` | logging | `logging.default`, `logging.channels.single.level`, `logging.channels.slack.*` | No | Settings > Logging |
| `BillingSettings` | billing | `billing.*` | Yes | Settings > Billing |
| `MailSettings` | mail | `mail.*` | Yes | Settings > Mail |
| `TenancySettings` | tenancy | `tenancy.*` | No | Settings > Tenancy |
| `StripeSettings` | stripe | `stripe.*` | Yes | Settings > Stripe |
| `PaddleSettings` | paddle | `paddle.*` | Yes | Settings > Paddle |
| `LemonSqueezySettings` | lemon-squeezy | `lemon-squeezy.*` | Yes | Settings > Lemon Squeezy |
| `IntegrationsSettings` | integrations | `services.*` | No | Settings > Integrations |
| `PrismSettings` | prism | `prism.*` | Yes | Settings > Prism |
| `AiSettings` | ai | `ai.*` | Yes | Settings > AI |
| `ScoutSettings` | scout | `scout.*` | No | Settings > Search |
| `MemorySettings` | memory | `memory.*` | No | Settings > Memory |
| `SecuritySettings` | security | `csp.*`, `honeypot.*`, `app.ip_whitelist` | No | Settings > Security |
| `CookieConsentSettings` | cookie-consent | `cookie-consent.*` | No | Settings > Cookie Consent |
| `PerformanceSettings` | performance | `responsecache.*` | No | Settings > Performance |
| `MonitoringSettings` | monitoring | `sentry.*`, `telescope.*` | No | Settings > Monitoring |
| `FeatureFlagSettings` | feature-flags | `feature-flags.*` | No | Settings > Feature Flags |
| `FilesystemSettings` | filesystem | `filesystems.*` | No | Settings > Filesystem |
| `BroadcastingSettings` | broadcasting | `broadcasting.connections.reverb.*` | No | Settings > Broadcasting |
| `PermissionSettings` | permission | `permission.*` | No | Settings > Permissions |
| `ActivityLogSettings` | activitylog | `activitylog.*` | No | Settings > Activity Log |
| `ImpersonateSettings` | impersonate | `filament-impersonate.*` | No | Settings > Impersonate |
| `BackupSettings` | backup | `backup.*` | No | Settings > Backup |
| `MediaSettings` | media | `media-library.*` | No | Settings > Media |
| `SetupWizardSettings` | setup-wizard | — | No | Internal (setup completion state) |

#### `AuthSettings` properties

| Property | Type | Default | Notes |
|---|---|---|---|
| `registration_enabled` | `bool` | `true` | Toggle public registration |
| `email_verification_required` | `bool` | `false` | Require email confirmation before login |
| `google_oauth_enabled` | `bool` | `false` | Enable Google social login |
| `google_client_id` | `string` | `''` | Overlaid → `services.google.client_id` |
| `google_client_secret` | `string` | `''` | Encrypted; overlaid → `services.google.client_secret` |
| `github_oauth_enabled` | `bool` | `false` | Enable GitHub social login |
| `github_client_id` | `string` | `''` | Overlaid → `services.github.client_id` |
| `github_client_secret` | `string` | `''` | Encrypted; overlaid → `services.github.client_secret` |
| `two_factor_enforcement` | `string` | `'optional'` | `optional` \| `required` \| `admins_only` |
| `session_lifetime` | `int` | `120` | Minutes; overlaid → `session.lifetime` |
| `password_min_length` | `int` | `8` | Minimum password character count |
| `password_require_uppercase` | `bool` | `false` | Enforce uppercase in passwords |
| `password_require_numbers` | `bool` | `false` | Enforce digit in passwords |
| `password_require_symbols` | `bool` | `false` | Enforce symbol in passwords |

## Key components

### `SettingsOverlayServiceProvider`

- Registered in `bootstrap/providers.php`.
- Contains `OVERLAY_MAP` constant: maps each settings class to its `config()` keys and whether org overrides are allowed.
- `boot()` iterates the map, resolves each settings class in a try-catch, and writes values into `config()`.
- `applyOverlay()` — static method, callable from tests to re-apply after DB is ready.
- `orgOverridableKeys()` — returns only the org-overridable settings keys (used by the middleware).

### `ApplyOrganizationSettings` middleware

- Registered in `bootstrap/app.php` web + API middleware, after `SetTenantContext`.
- Reads current org from `TenantContext::get()`.
- Calls `OrganizationSettingsService::applyForOrganization()` with the overridable keys.

### `OrganizationSettingsService`

Methods:
- `applyForOrganization(Organization $org, array $overridableKeys)` — loads org overrides from cache, writes into `config()`.
- `getOverridesForOrganization(Organization $org)` — returns all overrides (cached 60 min, key `org_settings:{id}`).
- `setOverride(Organization $org, string $group, string $name, mixed $value, bool $encrypt)` — upsert a single override.
- `removeOverride(Organization $org, string $group, string $name)` — delete a single override.
- `clearCache(Organization $org)` — invalidate the cache for an org.

### `organization_settings` table

```sql
organization_settings (
    id, organization_id, group, name, payload JSON,
    is_encrypted BOOL DEFAULT FALSE,
    created_at, updated_at,
    UNIQUE(organization_id, group, name),
    FK organization_id → organizations.id CASCADE
)
```

Encrypted values use `Crypt::encryptString(json_encode($value))` and are decrypted transparently by the service.

## Filament UI

- **Settings pages**: 29 pages in `app/Filament/Pages/Manage*.php` extending `SettingsPage`.
- **Organization Overrides**: `ManageOrganizationOverrides` — a custom Page with `HasTable` showing all per-org overrides. Has "Add Override" action and delete per-row.
- **Creation**: `php artisan make:filament-settings-page PageName "App\\Settings\\SettingsClass" --generate`
- Form field names must match the property names on the settings class.
- Secret fields use `TextInput::make('...')->password()->revealable()`.

## Usage in app code

Inject the settings class or resolve from the container:

```php
$app = app(AppSettings::class);
$siteName = $app->site_name;

// Or use config() — the overlay makes this work
$siteName = config('app.name'); // reads from DB via overlay
```

For settings without a config target (AuthSettings, SeoSettings), resolve directly:

```php
$auth = app(AuthSettings::class);
if (!$auth->registration_enabled) {
    abort(403);
}
```

## Adding a new settings group

1. Create the settings class: `php artisan make:class "Settings/FeatureSettings"`
   - Extend `Spatie\LaravelSettings\Settings`, define `group()`, add typed properties.
   - Add `public static function encrypted(): array` if it has secrets.
2. Create the migration: `php artisan make:settings-migration FeatureSettings --no-interaction`
   - Use `$this->migrator->add('group.property', config('key', default))`.
   - Use `$this->migrator->addEncrypted(...)` for secrets.
3. Add to `SettingsOverlayServiceProvider::OVERLAY_MAP` with property-to-config-key mapping and `orgOverridable` flag.
4. Create Filament page: `php artisan make:filament-settings-page ManageFeature "App\\Settings\\FeatureSettings" --generate --no-interaction`
5. Run `php artisan migrate`.

## Making a setting org-overridable

1. In `OVERLAY_MAP`, set `'orgOverridable' => true` for the group.
2. The middleware will automatically pick it up via `orgOverridableKeys()`.
3. Admins can set per-org values in Filament → Settings → Organization Overrides.

## Artisan commands

| Command | Purpose |
|---------|---------|
| `settings:cache` | Warm the org settings cache for all organizations |
| `settings:clear-cache [--org=ID]` | Clear the org settings cache (all or specific org) |
| `app:install` | Run initial application setup (app, mail, optional AI) and mark setup complete; see [Setup wizard and CLI install](#setup-wizard-and-cli-install) below. |

### Setup wizard and CLI install

Initial configuration is driven by **Setup Wizard** (`SetupWizardSettings`). Super-admins are redirected to the Filament setup wizard at `/system/setup-wizard` until setup is marked complete. The wizard writes to the same settings classes (App, Mail, Billing, Stripe, Prism, AI) and calls `SettingsOverlayServiceProvider::applyOverlay()` on completion.

For **CI, headless, or scripted installs**, use the Artisan command:

```bash
php artisan app:install --non-interactive
```

- **`--non-interactive`** — Use defaults or option values; no prompts. Idempotent: if setup is already complete, exits 0 without writing.
- **Options** (optional): `--site-name=`, `--url=`, `--mail-mailer=`, `--mail-from=`, `--mail-from-name=`.

Without `--non-interactive`, the command prompts for site name, URL, mailer (and SMTP details if needed), from address/name, and optionally AI provider and API key. All values are persisted to the `settings` table only (no `.env` changes).

## Caching

- **Global settings**: Spatie's built-in cache (`SETTINGS_CACHE_ENABLED=true`). Invalidated automatically on Filament save. Clear with `php artisan settings:clear-cache`.
- **Org overrides**: Laravel cache key `org_settings:{org_id}`, TTL 60 min. Invalidated on save via `OrganizationSettingsService::clearCache()`.
- **Queue workers**: Restart after global settings change, or use `settings:cache` to pre-warm.

## Testing

Use the `InteractsWithSettings` trait in `tests/Traits/`:

```php
use Tests\Traits\InteractsWithSettings;

uses(InteractsWithSettings::class);

it('applies org override', function () {
    $org = Organization::factory()->create();
    $this->setOrgOverride($org, 'mail', 'from_address', 'custom@example.com');

    // Assert config was updated
    expect(config('mail.from.address'))->toBe('custom@example.com');
});
```

Trait methods: `fakeSettings(string $class, array $overrides)`, `setOrgOverride(...)`, `clearOrgOverrides(Organization $org)`.

## Initial setup (setup_completed)

When setup has not been completed, super-admins are redirected to the **App** settings page to configure app identity (site name, timezone, locale). All other configuration (mail, billing, AI, etc.) is done from the Settings menu.

### Architecture

```
SetupWizardSettings (group: setup-wizard)
    ├── setup_completed: bool (default false)
    └── completed_steps: array

EnsureSetupComplete middleware (Filament auth middleware)
    ├── Only intercepts authenticated super-admin users
    ├── Checks SetupWizardSettings::setup_completed
    ├── Excludes the App settings page + logout routes
    └── Redirects to /admin/manage-app if not complete

ManageApp (Settings > Platform > App)
    └── On first save when setup not complete: sets setup_completed=true, applies overlay
```

### Key files

| File | Purpose |
|------|---------|
| `app/Settings/SetupWizardSettings.php` | Tracks setup completion state |
| `database/settings/2026_02_23_100000_create_setup_wizard_settings.php` | Seeds defaults |
| `app/Http/Middleware/EnsureSetupComplete.php` | Redirects super-admins to App settings page |
| `app/Filament/Pages/ManageApp.php` | App settings; afterSave() marks setup complete on first save |

### Middleware

`EnsureSetupComplete` is registered in `AdminPanelProvider->authMiddleware()`, scoped to the Filament panel only. It wraps settings resolution in a `try/catch` so fresh installs (where the `settings` table doesn't exist yet) skip the check gracefully.

### Behaviour

- When `setup_completed` is false, super-admins are sent to **Settings > App** (`/admin/manage-app`).
- Saving the App settings page sets `setup_completed = true` and applies the config overlay; the super-admin can then use the panel normally.
- All other settings (Mail, Billing, AI, etc.) are configured from the Settings menu; there is no multi-step wizard in the main flow.
- The legacy Setup Wizard page (`/admin/setup-wizard`) still exists but is hidden from navigation (`shouldRegisterNavigation = false`).

### Env slimming

Config files no longer use `env()` for DB-backed settings. Instead, they use hardcoded defaults that are overwritten by the settings overlay at boot. Only infrastructure vars that are needed before the DB is available remain in `.env` (see "What stays in `.env`" above).

## Migrations

- **Table**: `settings` (created by the package migration).
- **Settings migrations**: Stored in `database/settings/`. Default values are seeded from `config()`/`env()` during migration — no separate seeder needed.

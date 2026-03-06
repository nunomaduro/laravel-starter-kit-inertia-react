# Filament Admin and System Panels

## Purpose

The application has two Filament panels:

- **Admin panel** (`/admin`): For **org admins** and **super-admins**. User management, roles, permissions, content (posts, categories, help, changelog, contact, enterprise), organization invitations, vouchers. Org switcher in the sidebar when tenancy is enabled. Access: `access admin panel` or super-admin.
- **System panel** (`/system`): **Super-admin only**. Platform-wide settings and data: Organizations list, all Manage* settings (App, Auth, Mail, Theme, Tenancy, Billing, Stripe, Paddle, Lemon Squeezy, Prism, AI, Backup, Security, etc.), Activity Log, Feature Segments, Credit Packs, Mail Templates, Terms Versions, Affiliates, Revenue Analytics, API docs, Product Analytics, Setup Wizard, Organization Overrides. No org switcher. Access: super-admin only (`EnsureSuperAdmin` middleware).

Resources and pages use `canAccess()` with `filament()->getCurrentPanel()?->getId()` so each item appears only in the correct panel. Activity Log and Feature Flag plugins are registered only on the System panel.

## Auth

- **Spatie Laravel Permission**: Roles `super-admin`, `admin`, and `user`. Permission `access admin panel` plus resource-level permissions (`view users`, `create users`, `edit users`, `delete users`).
- **Panel access**: `FilamentUser::canAccessPanel` checks `$user->can('access admin panel')`. Super-admins bypass via `Gate::before` in `AppServiceProvider`; admins have the permission.
- **Guard**: `web` (same as Fortify).

## Dev credentials

For local development, use seeded user **admin@example.com** / **password** (role `super-admin`). Log in at `/admin` (or `/system` for the System panel).

## Creating resources

```bash
php artisan make:filament-resource Model --generate --view
```

Use **policies** and **permissions** for authorization. Prefer `$user->can(...)` in policies; super-admin is handled by `Gate::before`.

## Generators

- `php artisan make:filament-resource Model --generate --view` — Resource with form, infolist, table, pages.
- `php artisan make:filament-relation-manager ResourceName relationName --attach` — Relation manager for a resource.
- `php artisan make:filament-widget WidgetName` — Generic widget; add `--stats-overview` for stats, `--chart`, or `--table`.
- `php artisan make:filament-page PageName` — Custom Filament page.

## Config

- **Panel**: `app/Providers/Filament/AdminPanelProvider.php` (path, guard, login, branding, global search, dark mode, max width, database notifications).
- **Filament**: `config/filament.php`.

## StateFusion & data packages

- **StateFusion**: `a909m/filament-statefusion` is registered in `AdminPanelProvider`. Use it when you need Filament form/table state to sync with Livewire or URL (see [Search & Data](search-and-data.md)).
- **Sortable, Sluggable, Model Flags, Model States, Schemaless**: Available for resources when needed; see [Search & Data](search-and-data.md) for DTOs, Sluggable, Sortable, flags, states, and schemaless attributes.

## DX features

- **Branding**: `brandName`, `brandLogo`, `favicon` in `AdminPanelProvider`; app name and `public/logo.svg`, `public/favicon.svg` by default.
- **Global search**: Panel `globalSearch()`; resources override `getGloballySearchableAttributes()` (e.g. `UserResource`: `['name', 'email']`).
- **Dark mode**: `darkMode()` on panel; users can toggle.
- **Dashboard widget**: `App\Filament\Widgets\StatsOverviewWidget` (e.g. user count with link to users list); discovered via `app/Filament/Widgets`.
- **Table defaults**: Default sort, per-page options, search debounce — e.g. `UsersTable` (`defaultSort`, `paginationPageOptions`, `searchDebounce`).
- **Database notifications**: `databaseNotifications()` on panel; `notifications` table required (`php artisan notifications:table` + migrate).

## User impersonation

**Package**: `stechstudio/filament-impersonate` (v5+, native implementation; no longer uses `lab404/laravel-impersonate`).

- **Who can impersonate**: **Super-admin** or **org admin** (admin/owner in at least one organization), when the Impersonation feature is active (`User::canImpersonate()`).
- **Who can be impersonated**: Super-admins cannot be impersonated. **Super-admin** may impersonate any other user. **Org admin** may impersonate only users in the same organization(s) (`User::canBeImpersonated()`; impersonator is `auth()->user()` at click time).
- **Where**: Impersonate action is on the Users table (row action) and on the Edit User page (header action). Visibility uses `canImpersonate()`; the package still enforces `canBeImpersonated()` on the target. The **Users list** is scoped: super-admins see all users; org admins and others see only users that share at least one organization (`UserResource::getEloquentQuery()`). After impersonating, the admin is redirected to `/dashboard` as that user.
- **Banner**: When impersonating, a banner is shown (Filament panel and main app via `<x-impersonate::banner />` in `resources/views/app.blade.php`) with a “Leave” link that returns to the admin panel.
- **Activity log**: Start and end of impersonation are logged with causer = impersonator, subject = impersonated user, and properties `impersonator_name`, `impersonated_name`, `impersonator_id`, `impersonated_id` (see `App\Enums\ActivityType::ImpersonationStarted`, `ImpersonationEnded` and `App\Listeners\LogImpersonationEvents`). **Actions taken while impersonating** (model changes, RBAC logs, etc.) use the **impersonator** as causer: `AppServiceProvider::registerActivityLogImpersonationCauser()` configures Spatie's `CauserResolver` so that when `Impersonation::isImpersonating()`, the default causer is the impersonator; `ActivityLogRbac` uses that resolver for its causer. Events: `STS\FilamentImpersonate\Events\EnterImpersonation`, `LeaveImpersonation`.
- **Policy**: `UserPolicy::viewAny()` returns `true` when `Impersonation::isImpersonating()` (via `STS\FilamentImpersonate\Facades\Impersonation`) to avoid 403s on the users list during impersonation.
- **Routes**: The package registers `filament-impersonate.leave` for the banner. Impersonation is started only via the Filament Impersonate action (no standalone take route).

## Settings (database-backed)

**Plugin**: `filament/spatie-laravel-settings-plugin` — Settings pages extend `Filament\Pages\SettingsPage` and are listed under the **Settings** navigation group (App, Auth, SEO). Each page is bound to a settings class in `App\Settings\*`. See [Settings](settings.md) for creating and using settings.

## Feature flags

**Plugin**: `stephenjude/filament-feature-flags` — registered as `FeatureFlagPlugin::make()` in `AdminPanelProvider`. Provides a “Manage Features” (or custom label) resource under the Settings group where admins can enable/disable Pennant class-based features globally or per segment (e.g. by user email). See [Feature flags](feature-flags.md) for defining features and exposing them to Inertia.

## Testing

Use the `actsAsFilamentAdmin(TestCase $test, string $role = 'admin'): User` helper in Pest feature tests when you need an authenticated admin or super-admin. It seeds `RolesAndPermissionsSeeder`, creates a user with the given role, calls `$this->actingAs($user)`, and returns the user. Example:

```php
actsAsFilamentAdmin($this);
$response = $this->get('/admin');
$response->assertOk();
```

See `tests/Feature/Filament/AdminPanelAccessTest.php` and `tests/Pest.php` for the helper definition.

## Deploy

For production, run `composer run optimize-production` (or `php artisan config:cache`, `route:cache`, `filament:cache-components` in your deploy pipeline). Do not add `filament:cache-components` to `post-autoload-dump` or `post-update-cmd` so local dev stays unchanged.

## Filament Blueprint (optional)

Filament Blueprint is a premium Laravel Boost extension that helps AI agents produce detailed Filament implementation plans. The project works without it.

**To install:** Set `FILAMENT_BLUEPRINT_EMAIL` and `FILAMENT_BLUEPRINT_LICENSE_KEY` in `.env` (see `.env.example`), then run:

```bash
composer setup-blueprint
```

Or run `scripts/setup-filament-blueprint.sh` directly. When prompted during `boost:install`, select **Filament Blueprint**. If the env vars are missing, the script skips install and exits 0.

## Links

- [Filament v5 docs](https://filamentphp.com/docs/5.x)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6)

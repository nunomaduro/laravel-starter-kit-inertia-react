# Single-Tenant Mode

The application can run in **single-tenant** (internal) or **multi-tenant** (SaaS) mode. When single-tenant mode is enabled, the UI hides organization management and the app behaves like an internal company tool.

## Configuration

Tenancy is **DB-backed** via **Filament Settings > Tenancy** (`TenancySettings`). The overlay in `SettingsOverlayServiceProvider` writes these values into `config('tenancy.enabled')` (and related keys) at boot. There is no `.env` key for tenancy in this kit—configure it in the admin panel after the app is installed.

- **Default** (fresh install): `config/tenancy.php` has `'enabled' => true` until the settings table is migrated and overlay runs; then DB values take over.
- To run in single-tenant mode: in Filament go to **Settings > Tenancy** and set multi-organization mode to **disabled**.

## What Changes

| Aspect | Multi-tenant (default) | Single-tenant |
|--------|------------------------|---------------|
| **Personal org creation** | Yes (on registration) | Yes (unchanged) |
| **Org switcher** | Shown | Hidden |
| **Organizations nav item** | Shown | Hidden |
| **Organization routes** | Accessible | Redirect to dashboard |
| **Billing** | Per-org | Per user's default org |
| **Tenant context** | Set from session/domain | User's single (default) org |

The database schema and internal logic stay the same. Users still have a personal organization; it is just not exposed in the UI.

## Implementation Details

- **`HandleInertiaRequests`** – Shares `tenancy_enabled` in `auth`; when false, `organizations` is empty (hides switcher) but `current_organization` is still set for billing and tenant-scoped logic.
- **`CreatePersonalOrganizationOnUserCreated`** – Always creates a personal org when `auto_create_personal_organization` is true, regardless of tenancy mode.
- **`EnsureTenancyEnabled`** – Middleware applied to organization routes; redirects to dashboard when tenancy is disabled.
- **Sidebar** – Organizations nav item has `tenancyRequired: true` and is hidden when `tenancy_enabled` is false.
- **OrganizationSwitcher** – Renders only when `tenancy_enabled`; also hidden because `organizations` is empty in single-tenant mode.
- **Filament** – Tenant menu (sidebar/topbar) is hidden when tenancy is disabled via published Blade views.

## When to Use

- **Single-tenant** – Internal tools, company software, one organization.
- **Multi-tenant** – SaaS, multiple customers/workspaces, B2B.

## Related

- [Billing & Multi-Tenancy](./billing-and-tenancy.md) – Seat billing, domain resolution
- [Visibility & Sharing](./visibility-sharing.md) – Global/org/shared data

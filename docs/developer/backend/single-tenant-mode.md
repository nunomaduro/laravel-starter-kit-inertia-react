# Single-Tenant Mode

The application can run in **single-tenant** (internal) or **multi-tenant** (SaaS) mode. When single-tenant mode is enabled, the UI hides organization management and the app behaves like an internal company tool.

## Configuration

Set in `.env`:

```env
MULTI_ORGANIZATION_ENABLED=false
```

Or in `config/tenancy.php`:

```php
'enabled' => env('MULTI_ORGANIZATION_ENABLED', true),
```

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

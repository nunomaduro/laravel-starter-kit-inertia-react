# Site-wide announcements (Announcements Module)

> **Module location:** `modules/announcements/` — enable/disable via `config/modules.php` or `php artisan module:enable announcements`.

Site-wide announcements let super-admins and org admins display dismissible banners to users. Announcements can be **global** (all users) or **organization-scoped** (only members of a given organization).

## Hierarchy

- **Super-admin:** Can create, edit, and delete **global** and **organization** announcements. Sees all announcements in the admin list. Can choose scope and organization when creating.
- **Org admin:** Can create, edit, and delete only **organization** announcements for the current tenant. Cannot create or edit global announcements. Sees global announcements plus the current org’s announcements in the list.
- **Other users:** Cannot manage announcements; they only see active announcements in the app banner.

## Model and database

- **Model:** `Modules\Announcements\Models\Announcement`
- **Table:** `announcements` — `id`, `title`, `body`, `level` (info, warning, maintenance), `scope` (global, organization), `organization_id` (nullable), `starts_at`, `ends_at`, `is_active`, `created_by`, `timestamps`
- **Enums:** `Modules\Announcements\Enums\AnnouncementLevel` (Info, Warning, Maintenance), `Modules\Announcements\Enums\AnnouncementScope` (Global, Organization)
- **Scope:** `Announcement::active()` — `is_active` true, `starts_at` in the past or null, `ends_at` in the future or null

## Permissions

- **announcements.manage_global** — Super-admin only (assigned in `RolesAndPermissionsSeeder`). Required to create or edit global announcements and to choose scope/org in the Filament form.
- **announcements.manage** — Org-scoped (in `database/seeders/data/organization-permissions.json` under `org_announcements`). Granted to owner and admin. Required to create or edit organization announcements for the current tenant.

Run `php artisan permission:sync` after changing organization permissions so org roles receive the new permission.

## Policy

- **Modules\Announcements\Policies\AnnouncementPolicy:** `viewAny` / `view` allow any authenticated user. `create` allows users with `announcements.manage_global` or `announcements.manage` in the current org (when tenant context is set). `update` / `delete` allow super-admin for any announcement; org admin only for organization-scoped announcements belonging to their org.

## Filament

- **Resource:** `Modules\Announcements\Filament\Resources\Announcements\AnnouncementResource` (admin panel, Content group)
- **Access:** `canAccess()` — user has `announcements.manage_global` or has `announcements.manage` in the current organization.
- **Query:** Super-admin sees all announcements; others see global plus current tenant’s announcements (`getEloquentQuery()`).
- **Form:** Title, body, level, scope (super-admin only), organization (super-admin only when scope is organization), is_active, starts_at, ends_at. Non–super-admin create flow sets scope to organization and organization_id to current tenant in `CreateAnnouncement::mutateFormDataBeforeCreate()`.

## Shared data and frontend

- **Shared key:** `announcements` — set in `HandleInertiaRequests::share()`. Resolved by `resolveAnnouncements()`: active announcements for the current user (global + current org), ordered global first then by created_at. Only shared when the user is authenticated.
- **Component:** `resources/js/components/announcements-banner.tsx` — reads `announcements` from Inertia shared props, renders a dismissible Alert per announcement (styled by level). Dismissal is per-session (useState).
- **Layout:** Rendered in `resources/js/layouts/app/app-sidebar-layout.tsx` so the banner appears on all app layout pages.
- **Pan:** Banner container has `data-pan="announcements-banner"`; name is registered in `AppServiceProvider::configurePan()`.

## Multi-tenancy

When `MULTI_ORGANIZATION_ENABLED=false`, tenant context may still be used for a single organization; behavior is unchanged (global + that org’s announcements). The announcements module does not add separate single-tenant logic.

## Tests

- **tests/Feature/AnnouncementPolicyTest.php** — Policy: super-admin can create/update/delete; org admin can create when tenant set and update own org’s announcements only; member cannot create.
- **tests/Feature/AnnouncementsTest.php** — Shared props: dashboard receives active announcements; inactive/expired announcements are excluded.

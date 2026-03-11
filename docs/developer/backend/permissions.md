# Permissions and RBAC

The application uses [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) with optional **route-based permissions**, **permission categories** (wildcard grouping), and **role hierarchy** (super-admin bypass).

## Named routes (required)

All application routes must have a name so that `permission:sync-routes` can create matching permissions. Unnamed routes are skipped by the sync and will not be enforced when route-based permissions are on.

- **Enforce in CI**: Run `php artisan permission:check-routes`. It lists unnamed app routes and exits with code 1 when `config('permission.require_named_routes')` is true (default) or when you pass `--strict`.
- **Config**: Set `PERMISSION_REQUIRE_NAMED_ROUTES=false` to only report unnamed routes without failing.
- **Convention**: Use dot notation (e.g. `users.index`, `settings.password.update`). Add public/auth routes to `route_skip_patterns` in `config/permission.php` so they are not gated.

## Overview

- **Roles**: `super-admin`, `admin`, `user` (seeded in `RolesAndPermissionsSeeder`).
- **Core permissions**: `bypass-permissions`, `access admin panel`, `view users`, `create users`, `edit users`, `delete users`.
- **Super-admin**: Has `bypass-permissions`; `Gate::before` lets them pass all permission checks except dangerous User model operations (delete/forceDelete), which are delegated to `UserPolicy`.

## Configuration

- **`config/permission.php`** (Spatie) plus:
  - **`route_based_enforcement`** – When `true`, `AutoPermissionMiddleware` runs on web routes and requires a permission matching the route name for named app routes not in the skip list. Set via `PERMISSION_ROUTE_BASED_ENFORCEMENT` (default: `false`).
  - **`route_skip_patterns`** – Route name patterns to skip (e.g. `login`, `password.*`, `dashboard`, `filament.*`). Use `*` for wildcard.
  - **`default_role`** – Role name assigned to newly registered or Filament-created users when no role is set. Set via `PERMISSION_DEFAULT_ROLE` (default: `user`). Set to `null` to disable. The role must exist (e.g. seeded).
  - **`default_role_permissions`** – Array of permission names to assign to the default role when seeding. Empty by default (authenticated routes in `route_skip_patterns` don’t require a permission). Set e.g. `['dashboard']` to give the default role baseline permissions and satisfy `permission:health`.
  - **`permission_categories_enabled`** – When `true`, `RolesAndPermissionsSeeder` uses `config/permission_categories.php` and `PermissionCategoryResolver` to assign permissions to roles by category/wildcard. Set via `PERMISSION_CATEGORIES_ENABLED` (default: `false`).
- **`config/permission_categories.php`** – Optional. Defines categories (patterns + roles) and per-role strategy (`bypass`, `categories`, `explicit`). Used only when `permission_categories_enabled` is true.

## Route-based permissions (dynamic RBAC)

1. **Sync permissions from routes**
   ```bash
   php artisan permission:sync-routes
   ```
   Creates/updates permissions from named application routes (skips patterns in `route_skip_patterns`). Use `--dry-run` to preview, `--prune` to remove permissions that no longer match any route.

2. **Enable enforcement**
   Set `PERMISSION_ROUTE_BASED_ENFORCEMENT=true` (or `config('permission.route_based_enforcement', true)`). Then `AutoPermissionMiddleware` runs on the web group and requires `hasPermissionTo($routeName)` for named app routes unless they are skipped or already have explicit `permission:`/`role:` middleware.

3. **Autonomous sync**
   - **After migrations**: When `route_based_enforcement` is true, `MigrationListener` runs `permission:sync-routes --silent` after migrations so new routes get permissions without a manual step.
   - **Scheduler**: When `route_based_enforcement` is true, `permission:sync-routes --silent` is scheduled daily in `routes/console.php` so permissions stay in sync even if routes are added without running sync manually.
   - **Deploy**: After `php artisan route:cache`, run `php artisan permission:sync-routes` so cached routes and permissions match (or rely on the daily schedule).

5. **Middleware alias**
   `auto.permission` is registered for optional use on specific route groups; when route-based enforcement is enabled, the middleware is appended to the web group globally.

## Permission categories (wildcard grouping)

- **Purpose**: Assign permissions to roles by pattern (e.g. `filament.admin.*`) instead of listing each permission.
- **Config**: `config/permission_categories.php` → `categories` and `roles`. Each role can use strategy `bypass`, `categories`, or `explicit`.
- **Resolver**: `App\Services\PermissionCategoryResolver::getPermissionsForRole(string $roleName)` returns the list of permission names for that role.
- **Seeder**: When `permission_categories_enabled` is true, `RolesAndPermissionsSeeder` uses the resolver for the admin role (and can be extended for others).

## Role hierarchy (super-admin bypass)

- **Implementation**: In `AppServiceProvider::boot()`, `Gate::before` checks if the user has the `bypass-permissions` permission (via direct assignment or through a role). If so, it returns `true` for all abilities **except** when the ability is `delete` or `forceDelete` and the first argument is an `App\Models\User` (then it returns `null` so `UserPolicy` runs).
- **Safety**: User delete/forceDelete always goes through `UserPolicy`, so you can enforce rules (e.g. cannot delete self, or require an extra permission) there.
- **Last super-admin protection**: The last user with the `super-admin` role cannot be deleted (`UserPolicy::delete` / `forceDelete` deny), and in Filament the last super-admin cannot have the super-admin role removed (validation error). This avoids locking the application out of admin access.
- **Seeder**: `RolesAndPermissionsSeeder` creates the `bypass-permissions` permission and assigns it to the `super-admin` role.

## Default role for new users

- When a user is created (registration via `CreateUser` action or Filament **Users** create), they are assigned the role in `config('permission.default_role')` (default: `user`) if that role exists and no role was explicitly set. Set `PERMISSION_DEFAULT_ROLE=null` or empty to disable.

## Sanctum API tokens with permission abilities

- Use `$user->createTokenWithPermissionAbilities('token-name')` to create a personal access token whose abilities match the user’s permissions. Users with `bypass-permissions` get `['*']`; others get their permission names. Check in API with `$request->user()->tokenCan('permission.name')`.

## Organization permissions (JSON-driven)

Organization-scoped permissions (`org.*`) are defined in `database/seeders/data/organization-permissions.json` and synced via `permission:sync`. Each org has `admin` and `member` roles; the owner implicitly has all org permissions.

- **Config**: `organization-permissions.json` defines permission names and which roles (`owner`, `admin`, `member`) get them.
- **Sync**: Run `php artisan permission:sync` to create Permission records and assign them to org roles. `RolesAndPermissionsSeeder` runs this automatically.
- **User methods** (via `HasOrganizationPermissions`): `canInOrganization()`, `canInCurrentOrganization()`, `canAnyInOrganization()`, `canAllInOrganization()`, `isOrganizationOwner()`, `isOrganizationAdmin()`, `hasOrganizationRole()`, `roleNamesInOrganization()`.
- **Blade directives**: `@canOrg`, `@cannotOrg`, `@canAnyOrg`, `@canAllOrg`, `@isOrgOwner`, `@isOrgAdmin`, `@isOrgMember`, `@isOrgRole`.

## Resource-level ownership (Governor)

For “can act on *this* resource” (e.g. only the org owner can transfer/delete the org; only the announcement creator or super-admin can edit an org announcement), the app uses **Governor** (genealabs/laravel-governor) alongside Spatie. Spatie stays the source of truth for roles and permissions; Governor is used for ownership and optional scopes (view/update/delete “own” only). See [Governor](governor.md) for setup, Governable trait, `governor_owned_by`, and how to integrate with policies.

## Artisan commands

| Command | Description |
|--------|-------------|
| `permission:sync` | Create org permissions from `organization-permissions.json` and assign to org roles. Options: `--dry-run`, `--silent`. |
| `permission:sync-routes` | Create/update permissions from named routes. Options: `--dry-run`, `--prune`, `--silent`. |
| `permission:check-routes` | List application routes that have no name; exit 1 when `require_named_routes` is true or `--strict`. Use in CI to enforce named routes. |
| `permission:health` | Check RBAC health: super-admin role exists, optionally warn on users with no roles or empty default role. Exit 1 on critical failure; use `--strict` to exit 1 on warnings too. |

## Middleware aliases

| Alias | Class | Use |
|-------|--------|-----|
| `permission` | Spatie `PermissionMiddleware` | Require one or more permissions. |
| `role` | Spatie `RoleMiddleware` | Require one or more roles. |
| `role_or_permission` | Spatie `RoleOrPermissionMiddleware` | Require any of the given roles or permissions. |
| `auto.permission` | `App\Http\Middleware\AutoPermissionMiddleware` | Require permission matching route name (used globally when route-based enforcement is on). |

## Frontend (Inertia)

- **Shared data**: `HandleInertiaRequests` shares `auth.permissions` (permission names), `auth.roles` (role names), and `auth.can_bypass` (true when user has `bypass-permissions`). Use these for conditional UI without extra requests.
- **Convention**: Permission name = route name for route-based permissions; use the same string in the frontend (e.g. `useCan('dashboard')`).
- **`useCan(permission)`**: Hook (in `@/hooks/use-can`) returns `true` if the current user has the given permission or has `can_bypass`. Accepts one permission string or an array (true if user has any).
- **`<Can permission="…">`**: Component (in `@/components/can`) renders children only when the user has the given permission(s). Uses shared `auth.permissions` and `auth.can_bypass`.

## Filament management UI

- **Roles** (`/admin/roles`): List, create, edit, view roles; assign permissions via checkbox list. **Duplicate** table action creates a new role with the same permissions (name: "Copy of …"). `App\Filament\Resources\Roles\RoleResource`.
- **Permissions** (`/admin/permissions`): List and view permissions; **Sync from routes** header action runs `permission:sync-routes`. No create/edit (permissions come from sync or seeders). `App\Filament\Resources\Permissions\PermissionResource`.
- **Users**: User form and infolist include role assignment (multi-select and badges). Users table shows a Roles column.

Access to the admin panel (and thus these resources) requires the `access admin panel` permission.

## Related

- [Filament Admin Panel](./filament.md) – Panel access is gated by `access admin panel` (and `User::canAccessPanel()`).
- [Database seeders](./database/seeders.md) – `RolesAndPermissionsSeeder` seeds roles and permissions.

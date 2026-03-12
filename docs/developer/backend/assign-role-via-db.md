# AssignRoleViaDb

## Purpose

Centralized role assignment via direct `model_has_roles` inserts using **role id** and team key, avoiding Spatie `assignRole()` / `syncRoles()` when team context can mis-bind role **names** into **bigint** columns on PostgreSQL.

## Location

`app/Support/AssignRoleViaDb.php`

## Methods

| Method | Use case |
|--------|----------|
| `assignGlobal(User, array $roleNames)` | Global roles (`organization_id` / team `0`) |
| `assignOrg(User, int $organizationId, string $roleName)` | Single org-scoped role (insert only; use after ensuring org roles exist) |
| `syncOrg(User, int $organizationId, string $roleName)` | Replace org-scoped roles for that org only (delete pivot rows for user+org, then insert one role by id) |

## Call sites

- `CreateUser`, `TransferOrganizationOwnershipAction`
- `AppInstallCommand` — first super-admin after `RolesAndPermissionsSeeder`
- `OrganizationMemberController::update` — member role change (replaces `syncRoles`)
- `UsersSeeder` / org actions that already used DB inserts

## Shared Inertia auth (HandleInertiaRequests)

`getRoleNames()` / `getAllPermissions()` are team-scoped. Super-admin lives in team `0`; when an org is in `TenantContext`, resolving without switching to team `0` hides global roles. **HandleInertiaRequests** detects super-admin at team `0`, then builds `auth.roles`, `auth.permissions`, and `auth.can_bypass` at team `0`, and sets every `features.*` to `true` for super-admins so the sidebar and command palette stay full.

## Related

- `Organization::addMember` — same insert-by-id pattern for new members
- `config/tenancy.php` — `seed_in_progress` to skip listeners during seed
- [Database seeders](database/seeders.md) — seeding and Scout collection driver

# Governor (genealabs/laravel-governor)

[genealabs/laravel-governor](https://github.com/GeneaLabs/laravel-governor) provides **resource-level ownership** and an optional ACL (entities, actions, roles, permissions) alongside Laravel’s authorization. This app uses **Spatie Laravel Permission** for role/permission; Governor is installed for **ownership** and optional scoping (e.g. “only my records”).

## What’s installed

- **Package:** `genealabs/laravel-governor` (composer).
- **Config:** `config/genealabs-laravel-governor.php` (auth model, URL prefix, layout).
- **Migrations:** Run from the package (`php artisan migrate --path=vendor/genealabs/laravel-governor/database/migrations`). Tables: `governor_roles`, `governor_entities`, `governor_actions`, `governor_ownerships`, `governor_permissions`, `governor_role_user`, `governor_teams`, etc.
- **Seeders:** `LaravelGovernorDatabaseSeeder` (entities, actions, ownerships, roles, SuperAdmin/Admin). Run once to populate Governor’s roles and entities.

## Relation to Spatie Permission

- **Spatie** remains the source of truth for **roles and permissions** (e.g. `bypass-permissions`, `announcements.manage`, org permissions). Use Spatie for “can this user do X?”.
- **Governor** is used for **ownership** and “can this user act on *this* resource?” (e.g. only the org owner can transfer the organization; only the announcement creator or super-admin can edit an org announcement). Use Governor’s **Governable** trait and `governor_owned_by` on models when you want Governor to drive ownership and scopes.

## Using Governor for ownership

1. **Model:** Add the `Governable` trait and a `governor_owned_by` column (foreign key to users).
2. **Owner:** Set `governor_owned_by` when creating/updating (e.g. to `created_by`, or to `owner_id` for organizations). Governor’s `CreatingListener` sets `governor_owned_by` to `auth()->id()` on create when present. You can keep existing columns like `owner_id` or `created_by` and backfill/sync them to `governor_owned_by` in a migration.
3. **Scopes:** Governable adds `scopeViewable()`, `scopeUpdatable()`, `scopeDeletable()`, etc., which filter by Governor permissions and ownership (“any” vs “own”). Use these in queries when you want Governor to enforce “only own.”
4. **Policy:** In your policy you can allow based on ownership (e.g. `$user->getKey() === $model->governor_owned_by` or `$model->ownedBy()->is($user)` if using Governable). You do **not** have to extend Governor’s base policy; keep using Laravel policies and add ownership checks where needed.
5. **User and Governor roles:** Governor has its own roles (e.g. SuperAdmin). If you use both Spatie and Governor, avoid adding Governor’s **Governing** trait to the User model if it would conflict with Spatie’s `HasRoles` (e.g. both define `roles()`). You can use Governor for ownership and scopes without attaching Governor roles to users.

## Models using Governable

- **Announcement:** Has `governor_owned_by`; backfilled from `created_by`. Update/delete allowed for super-admin, the owner (governor_owned_by), or users with `announcements.manage` / `announcements.manage_global` as before.

## Entities and UI

Governor’s seeders register **entities** from your policies (auto-discovered). The package includes a web UI (Bootstrap 3) under the URL prefix in config (`url-prefix`). Use it to assign Governor roles and “any”/“own” permissions per entity/action if you use Governor’s ACL. For ownership-only usage, you only need the trait and column; the UI is optional.

## Summary

- **Spatie:** Who can do what (permissions, org roles).
- **Governor:** Who “owns” which record and optional scopes (view/update/delete “own” only). Install and migrations/seeders are done; add Governable and `governor_owned_by` to models where you want Governor-driven ownership and document it in [Permissions](permissions.md).

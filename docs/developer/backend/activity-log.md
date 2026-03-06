# Activity Log

The application uses [Spatie Laravel Activity Log](https://spatie.be/docs/laravel-activitylog/v4/introduction) and the [Filament Activity Log](https://filamentphp.com/plugins/alizharb-activity-log) plugin to record user and model changes.

## What is logged

- **Model changes**: Updates to attributes on models that use the `LogsActivity` trait (e.g. `User`, `EmbeddingDemo`). Sensitive attributes (passwords, tokens, secrets, recovery codes, embeddings) are never logged.
- **Two-factor authentication**: Explicit activities for `two_factor_enabled`, `two_factor_disabled`, `two_factor_confirmed`, and `recovery_codes_regenerated` (via Fortify action wrappers).
- **Roles and permissions**: When roles are assigned or updated on users, and when permissions are assigned or updated on roles, activities `roles_updated`, `roles_assigned`, `permissions_updated`, and `permissions_assigned` are logged with old/new values and causer (when available). This includes: role assignment on user create (Filament and registration via `CreateUser`), role changes on user edit, permission changes on role create/edit, and permission assignment when a role is duplicated from the Roles table.
- **RBAC config (Role/Permission models)**: Creation and renames of Spatie `Role` and `Permission` records are logged as `role_created`, `role_updated`, `permission_created`, and `permission_updated` via `RoleActivityObserver` and `PermissionActivityObserver` (subject: the role or permission; properties include `name` and `guard_name`).
- **User impersonation**: When a super-admin or org admin starts or ends impersonating another user, `impersonation_started` and `impersonation_ended` are logged via `App\Listeners\LogImpersonationEvents` (causer: impersonator, subject: impersonated user; properties: `impersonator_name`, `impersonated_name`, `impersonator_id`, `impersonated_id`). **While impersonating**, all activity (model changes, RBAC, etc.) uses the **impersonator** as causer via `AppServiceProvider::registerActivityLogImpersonationCauser()` (Spatie `CauserResolver`) and `ActivityLogRbac` using that resolver. See Filament docs for how impersonation is enabled.
- **Request context**: IP address and user agent are added to each activity via the `SetActivityContextTap` (enabled in config and applied by `ActivityLogObserver`).

## Configuration

- **Spatie config**: `config/activitylog.php` (table name, default log name, `activity_logger_taps` for the context tap).
- **Filament plugin**: Activity Log resource appears in the admin panel under the **System** navigation group (label “Log” / “Logs”). The plugin is registered in `AdminPanelProvider`.

## Adding logging to a new model

1. **Preferred**: Create the model with `php artisan make:model:full {Name}`. The command injects the `LogsActivity` trait and a default `getActivitylogOptions()` that logs all attributes except those in `config('activitylog.sensitive_attributes')`.
2. **Manual**: Add `use Spatie\Activitylog\Traits\LogsActivity` and implement `getActivitylogOptions()` returning `LogOptions::defaults()->logOnlyDirty()->logOnly([...])` or `->logAll()->logExcept([...])`. Exclude any attribute listed in `config('activitylog.sensitive_attributes')` (default: `password`, `remember_token`, `two_factor_secret`, `two_factor_recovery_codes`, `embedding`, `api_token`).

## ActivityLogRbac service

`App\Services\ActivityLogRbac` centralizes RBAC-related activity logging (user role and role permission changes). It is used by Filament User and Role resources and by the Roles table duplicate action.

- **Methods**: `logRolesUpdated(User, oldRoleNames, newRoleNames)`, `logRolesAssigned(User, roleNames)`, `logPermissionsUpdated(Role, oldPermissionNames, newPermissionNames)`, `logPermissionsAssigned(Role, permissionNames)`.
- **Helpers**: `roleNamesFrom(User)` and `permissionNamesFrom(Role)` return name arrays for the given model.
- Activities are only logged when `auth()->user()` is present (causer).

## Implementing activity logging in new modules

When adding a **new module** (new model, new feature with Actions, or new Filament resource), follow this so activity logging is consistent and AI agents know what to do.

### New Eloquent model

1. **Preferred**: Create the model with `php artisan make:model:full {Name}` so `LogsActivity` and `getActivitylogOptions()` are injected and sensitive attributes are excluded.
2. **If the model already exists**: Add `use Spatie\Activitylog\Traits\LogsActivity` and implement `getActivitylogOptions()` with `LogOptions::defaults()->logOnlyDirty()->logAll()->logExcept(config('activitylog.sensitive_attributes'))`. Do not log any attribute in that config list.
3. **New sensitive attribute**: Add the attribute name to `config('activitylog.sensitive_attributes')` so all models (and `make:model:full`) stay consistent.

### New custom event (e.g. in an Action)

When an Action or service performs an **important state change** that should be auditable (e.g. “subscription_activated”, “invite_sent”):

1. Add a new case to `App\Enums\ActivityType` (e.g. `case SubscriptionActivated = 'subscription_activated';`).
2. Log after the change: `activity()->performedOn($subject)->withProperties([...])->log(ActivityType::SubscriptionActivated->value)`. Set `->causedBy($user)` when an authenticated user caused the action.
3. In tests, assert with `assertActivityLogged(ActivityType::SubscriptionActivated->value, SubjectModel::class, (int) $subject->getKey())`.
4. Optionally document the new event in this file under “What is logged”.

### Filament resource or table action that changes important state

- **User/role/permission changes**: Use `App\Services\ActivityLogRbac` (e.g. `logRolesAssigned`, `logPermissionsAssigned`) in the resource’s `afterCreate` / `afterSave` or in the table action. Do not invent new ad‑hoc activity strings for RBAC; use the existing service.
- **Other important state** (e.g. “project_archived” from a table action): Add an `ActivityType` case and log as in “New custom event” above, with `->causedBy(auth()->user())` when applicable.

### Testing

- For **model updates**: Rely on Spatie’s automatic logging; assert that the expected `Activity` exists (e.g. `where('description', 'updated')`) and that sensitive keys are not in `properties`.
- For **custom events**: Use `assertActivityLogged(string $description, ?string $subjectType = null, ?int $subjectId = null)` from `tests/Pest.php` with the `ActivityType::...->value` and subject model/id.

### Checklist for agents

- [ ] New model → use `make:model:full` or add `LogsActivity` + `getActivitylogOptions()` excluding `config('activitylog.sensitive_attributes')`.
- [ ] New auditable event → add `ActivityType` case, log with `activity()->performedOn(...)->withProperties(...)->log(ActivityType::X->value)`, and test with `assertActivityLogged`.
- [ ] Filament RBAC changes → use `ActivityLogRbac`; do not add new free-form activity strings for roles/permissions.
- [ ] New sensitive attribute → add to `config('activitylog.sensitive_attributes')`.

## DX quick reference

- **Sensitive attributes**: Single source of truth is `config('activitylog.sensitive_attributes')`. Used by `make:model:full` and as reference for manual `getActivitylogOptions()`.
- **Custom event types**: Use `App\Enums\ActivityType` for description strings (e.g. `ActivityType::RolesAssigned->value`) so they stay consistent and IDE-friendly. Add new cases when you introduce new custom events.
- **Logging a custom event**: `activity()->performedOn($subject)->withProperties([...])->log(ActivityType::YourType->value)` and optionally `->causedBy($user)` when a causer exists.
- **Tests**: Use the `assertActivityLogged(string $description, ?string $subjectType = null, ?int $subjectId = null)` helper (defined in `tests/Pest.php`) to assert an activity was logged.
- **Status command**: Run `php artisan activitylog:status` to see which models use LogsActivity, all custom event types, and pointers to docs and the Filament log viewer.

## Viewing logs

In the Filament admin panel, open **System → Logs** to browse, filter, and search activity. The Filament plugin also supports timeline views, relation managers, and optional dashboard widgets.

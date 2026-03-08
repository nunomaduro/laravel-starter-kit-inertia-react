# RecordAuditLog

## Purpose

Records a structured audit log entry for any settings change, role grant/revoke, feature toggle, or administrative action.

## Location

`app/Actions/RecordAuditLog.php`

## Method Signature

```php
public function handle(
    string $action,
    ?string $subjectType = null,
    string|int|null $subjectId = null,
    mixed $oldValue = null,
    mixed $newValue = null,
    ?int $organizationId = null,
    ?int $actorId = null,
    string $actorType = 'user',
    ?string $ipAddress = null,
): AuditLog
```

## Dependencies

None (no constructor dependencies).

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$action` | `string` | Action key, e.g. `theme.saved`, `logo.uploaded`, `branding.user_controls.changed` |
| `$subjectType` | `string\|null` | Type of subject, e.g. `theme_setting`, `branding`, `user` |
| `$subjectId` | `string\|int\|null` | Identifier of the subject (setting name, user ID, etc.) |
| `$oldValue` | `mixed` | Previous value (scalar or array; scalar is wrapped in `['value' => ...]`) |
| `$newValue` | `mixed` | New value (scalar or array) |
| `$organizationId` | `int\|null` | Organization context; `null` for system-level actions |
| `$actorId` | `int\|null` | User ID of actor; defaults to `auth()->id()` |
| `$actorType` | `string` | Actor type: `'user'` or `'system'` |
| `$ipAddress` | `string\|null` | IP address; defaults to current request IP |

## Return Value

Returns the created `AuditLog` model instance.

## Usage Examples

### Theme save
```php
$this->auditLog->handle(
    action: 'theme.saved',
    subjectType: 'theme_setting',
    subjectId: 'org',
    newValue: $changed,
    organizationId: $organization->id,
);
```

### Logo upload
```php
$this->auditLog->handle(
    action: 'logo.uploaded',
    subjectType: 'theme_setting',
    subjectId: 'logo_path',
    newValue: ['path' => $path],
    organizationId: $organization->id,
);
```

## Related Components

- **Model**: `AuditLog`
- **Controller**: `OrgThemeController`, `OrgBrandingUserControlsController`
- **Route**: `settings.audit-log` (GET /settings/audit-log)
- **Filament**: `AuditLogResource` (system admin view)

## Notes

- The action string follows dot notation: `{domain}.{event}` (e.g. `theme.saved`, `branding.user_controls.changed`)
- Scalar `$oldValue`/`$newValue` are automatically wrapped in `['value' => ...]` for consistent JSON storage
- System admin can view all logs at `/admin/audit-logs`; org admins see only their own org at `/settings/audit-log`

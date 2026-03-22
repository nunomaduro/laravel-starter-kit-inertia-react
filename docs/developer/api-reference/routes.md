# API Reference

This document lists all available routes in the application.

**Last Updated**: 2026-03-19 15:46:17

## Closure

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `webhooks/mails/{provider}` | mails.webhook | - |
| GET | `filament/exports/{export}/download` | filament.exports.download | filament.actions |
| GET | `filament/imports/{import}/failed-rows/download` | filament.imports.failed-rows.download | filament.actions |
| GET | `admin/login` | filament.admin.auth.login | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| POST | `admin/logout` | filament.admin.auth.logout | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin` | filament.admin.pages.dashboard | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/billing/affiliates` | filament.admin.resources.billing.affiliates.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/categories` | filament.admin.resources.categories.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/credit-packs` | filament.admin.resources.credit-packs.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/credit-packs/create` | filament.admin.resources.credit-packs.create | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/credit-packs/{record}/edit` | filament.admin.resources.credit-packs.edit | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/enterprise-inquiries` | filament.admin.resources.enterprise-inquiries.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/enterprise-inquiries/{record}` | filament.admin.resources.enterprise-inquiries.view | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/enterprise-inquiries/{record}/edit` | filament.admin.resources.enterprise-inquiries.edit | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/mail-templates` | filament.admin.resources.mail-templates.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/mail-templates/{record}/edit` | filament.admin.resources.mail-templates.edit | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/organization-invitations` | filament.admin.resources.organization-invitations.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/organization-invitations/create` | filament.admin.resources.organization-invitations.create | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/organization-invitations/{record}/edit` | filament.admin.resources.organization-invitations.edit | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/roles` | filament.admin.resources.roles.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/roles/create` | filament.admin.resources.roles.create | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/roles/{record}` | filament.admin.resources.roles.view | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/roles/{record}/edit` | filament.admin.resources.roles.edit | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/users` | filament.admin.resources.users.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/users/create` | filament.admin.resources.users.create | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/users/{record}` | filament.admin.resources.users.view | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/users/{record}/edit` | filament.admin.resources.users.edit | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/vouchers` | filament.admin.resources.vouchers.index | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/vouchers/create` | filament.admin.resources.vouchers.create | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `admin/vouchers/{record}/edit` | filament.admin.resources.vouchers.edit | panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse |
| GET | `system/login` | filament.system.auth.login | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| POST | `system/logout` | filament.system.auth.logout | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system` | filament.system.pages.dashboard | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/user-activities-page` | filament.system.pages.user-activities-page | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/api-docs` | filament.system.pages.api-docs | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/billing/revenue-analytics` | filament.system.pages.billing.revenue-analytics | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-activity-log` | filament.system.pages.manage-activity-log | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-ai` | filament.system.pages.manage-ai | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-app` | filament.system.pages.manage-app | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-auth` | filament.system.pages.manage-auth | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-backup` | filament.system.pages.manage-backup | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-billing` | filament.system.pages.manage-billing | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-broadcasting` | filament.system.pages.manage-broadcasting | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-cookie-consent` | filament.system.pages.manage-cookie-consent | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-feature-flags` | filament.system.pages.manage-feature-flags | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-filesystem` | filament.system.pages.manage-filesystem | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-impersonate` | filament.system.pages.manage-impersonate | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-infrastructure` | filament.system.pages.manage-infrastructure | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-integrations` | filament.system.pages.manage-integrations | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-lemon-squeezy` | filament.system.pages.manage-lemon-squeezy | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-logging` | filament.system.pages.manage-logging | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-mail` | filament.system.pages.manage-mail | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-media` | filament.system.pages.manage-media | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-memory` | filament.system.pages.manage-memory | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-monitoring` | filament.system.pages.manage-monitoring | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-organization-overrides` | filament.system.pages.manage-organization-overrides | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-paddle` | filament.system.pages.manage-paddle | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-performance` | filament.system.pages.manage-performance | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-permissions` | filament.system.pages.manage-permissions | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-prism` | filament.system.pages.manage-prism | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-scout` | filament.system.pages.manage-scout | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-security` | filament.system.pages.manage-security | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-seo` | filament.system.pages.manage-seo | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-stripe` | filament.system.pages.manage-stripe | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-tenancy` | filament.system.pages.manage-tenancy | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/manage-theme` | filament.system.pages.manage-theme | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/analytics/product` | filament.system.pages.analytics.product | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/setup-wizard` | filament.system.pages.setup-wizard | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/feature-segments` | filament.system.resources.feature-segments.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/activity-logs` | filament.system.resources.activity-logs.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/activity-logs/{record}` | filament.system.resources.activity-logs.view | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/audit-logs` | filament.system.resources.audit-logs.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/organizations` | filament.system.resources.organizations.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/organizations/create` | filament.system.resources.organizations.create | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/organizations/{record}/edit` | filament.system.resources.organizations.edit | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/permissions` | filament.system.resources.permissions.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/permissions/{record}` | filament.system.resources.permissions.view | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/terms-versions` | filament.system.resources.terms-versions.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/terms-versions/create` | filament.system.resources.terms-versions.create | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/terms-versions/{record}/edit` | filament.system.resources.terms-versions.edit | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/visibility-demos` | filament.system.resources.visibility-demos.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/visibility-demos/create` | filament.system.resources.visibility-demos.create | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/visibility-demos/{record}/edit` | filament.system.resources.visibility-demos.edit | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/announcements` | filament.system.resources.announcements.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/announcements/create` | filament.system.resources.announcements.create | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/announcements/{record}/edit` | filament.system.resources.announcements.edit | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/posts` | filament.system.resources.posts.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/posts/create` | filament.system.resources.posts.create | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/posts/{record}` | filament.system.resources.posts.view | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/posts/{record}/edit` | filament.system.resources.posts.edit | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/changelog-entries` | filament.system.resources.changelog-entries.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/changelog-entries/create` | filament.system.resources.changelog-entries.create | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/changelog-entries/{record}` | filament.system.resources.changelog-entries.view | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/changelog-entries/{record}/edit` | filament.system.resources.changelog-entries.edit | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/contact-submissions` | filament.system.resources.contact-submissions.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/contact-submissions/{record}` | filament.system.resources.contact-submissions.view | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/contact-submissions/{record}/edit` | filament.system.resources.contact-submissions.edit | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/help-articles` | filament.system.resources.help-articles.index | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/help-articles/create` | filament.system.resources.help-articles.create | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/help-articles/{record}` | filament.system.resources.help-articles.view | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `system/help-articles/{record}/edit` | filament.system.resources.help-articles.edit | panel:system, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `api` | api | api |
| POST | `api/chat` | api.chat | api, auth:sanctum, throttle:30,1 |
| GET | `api/chat/memories` | chat.memories | api, auth:sanctum, throttle:30,1 |
| GET | `api/v1` | api.v1.info | api, throttle:60,1 |
| GET | `dev/components` | dev.components | web, auth, feature:component_showcase |
| GET | `dev/pages` | dev.pages | web, auth |
| GET | `favicon.ico` | favicon | web |
| GET | `robots.txt` | robots | web |
| GET | `/` | home | web |
| GET | `cookie-consent/accept` | cookie-consent.accept | web, feature:cookie_consent |
| GET | `legal/terms` | legal.terms | web |
| GET | `legal/privacy` | legal.privacy | web |
| GET | `dashboard` | dashboard | web, auth, verified |
| GET | `chat` | chat | web, auth, verified |
| POST | `organizations/switch` | organizations.switch | web, auth, verified |
| GET | `search` | search | web, auth, verified |
| POST | `settings/branding/user-controls` | settings.branding.user-controls | web, auth, verified |
| GET | `settings/audit-log` | settings.audit-log | web, auth, verified |
| GET | `profile/export-pdf` | profile.export-pdf | web, auth, verified |
| GET | `api/slug-availability` | api.slug-availability | web, auth, throttle:10,1 |
| GET | `internal/caddy/ask` | internal.caddy.ask | web, App\Http\Middleware\InternalRequestMiddleware |
| POST | `webhooks/stripe` | webhooks.stripe | web |
| POST | `webhooks/paddle` | webhooks.paddle | web |
| POST | `webhooks/spatie` | webhook-client-default | web |
| GET, POST, PUT, PATCH, DELETE | `settings` | settings | web, auth |
| GET | `settings/appearance` | appearance.edit | web, auth, feature:appearance_settings |
| GET | `settings/personal-data-export` | personal-data-export.edit | web, auth, feature:personal_data_export |
| POST | `settings/personal-data-export` | personal-data-export.store | web, auth, feature:personal_data_export |
| GET | `notifications` | notifications.index | web, auth |
| POST | `notifications/{notification}/read` | notifications.read | web, auth |
| POST | `notifications/read-all` | notifications.read-all | web, auth |
| DELETE | `notifications/{notification}` | notifications.delete | web, auth |
| DELETE | `notifications` | notifications.clear | web, auth |
| GET | `mcp/api` | - | - |
| DELETE | `mcp/api` | - | - |
| POST | `mcp/api` | - | Laravel\Mcp\Server\Middleware\ReorderJsonAccept, Laravel\Mcp\Server\Middleware\AddWwwAuthenticateHeader, auth:sanctum |
| POST | `_boost/browser-logs` | boost.browser-logs | - |
| GET | `pulse` | pulse | pulse |
| POST | `lemon-squeezy/webhook` | lemon-squeezy.webhook | - |
| GET | `livewire-02dd4205/js/{component}.js` | - | - |
| GET | `livewire-02dd4205/css/{component}.css` | - | - |
| GET | `livewire-02dd4205/css/{component}.global.css` | - | - |
| GET | `data-table/export/{table}` | data-table.export | web, auth |
| GET | `data-table/select-all/{table}` | data-table.select-all | web, auth |
| PATCH | `data-table/inline-edit/{table}/{id}` | data-table.inline-edit | web, auth |
| PATCH | `data-table/toggle/{table}/{id}` | data-table.toggle | web, auth |
| GET | `data-table/detail/{table}/{id}` | data-table.detail | web, auth |
| GET | `data-table/filter-options/{table}/{column}` | data-table.filter-options | web, auth |
| GET | `data-table/cascading-options/{table}/{column}` | data-table.cascading-options | web, auth |
| PATCH | `data-table/reorder/{table}` | data-table.reorder | web, auth |
| POST | `data-table/import/{table}` | data-table.import | web, auth |
| GET | `filament-excel/{path}` | filament-excel-download | web, signed |
| GET | `filament-impersonate/leave` | filament-impersonate.leave | web |
| POST | `help/{helpArticle}/rate` | help.rate | Illuminate\Routing\Middleware\SubstituteBindings, feature:help |
| GET | `storage/{path}` | storage.local | - |
| PUT | `storage/{path}` | storage.local.upload | - |
| GET | `docs/api` | scramble.docs.ui | web, Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess |
| GET | `docs/api.json` | scramble.docs.document | web, Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess |


## OpenHandlerController

**Controller**: `Fruitcake\LaravelDebugbar\Controllers\OpenHandlerController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `_debugbar/open` | debugbar.openhandler | Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope |
| GET | `_debugbar/clockwork/{id}` | debugbar.clockwork | Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope |

### handle

**Route**: `debugbar.openhandler`

**URI**: `_debugbar/open`

**Methods**: GET

**Middleware**: Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope

**Method Parameters**:
- `request`: `Fruitcake\LaravelDebugbar\Requests\OpenHandlerRequest`
- `debugbar`: `Fruitcake\LaravelDebugbar\LaravelDebugbar`
- `openHandler`: `DebugBar\OpenHandler`

### clockwork

**Route**: `debugbar.clockwork`

**URI**: `_debugbar/clockwork/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope

**Method Parameters**:
- `openHandler`: `DebugBar\OpenHandler`
- `id`: `mixed`


## CacheController

**Controller**: `Fruitcake\LaravelDebugbar\Controllers\CacheController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| DELETE | `_debugbar/cache/{key}` | debugbar.cache.delete | Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope |

### delete

**Route**: `debugbar.cache.delete`

**URI**: `_debugbar/cache/{key}`

**Methods**: DELETE

**Parameters**:
- `key`

**Middleware**: Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope

**Method Parameters**:
- `cache`: `Illuminate\Cache\CacheManager`
- `request`: `Fruitcake\LaravelDebugbar\Requests\CacheDeleteRequest`
- `key`: `string`


## QueriesController

**Controller**: `Fruitcake\LaravelDebugbar\Controllers\QueriesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `_debugbar/queries/explain` | debugbar.queries.explain | Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope |

### explain

**Route**: `debugbar.queries.explain`

**URI**: `_debugbar/queries/explain`

**Methods**: POST

**Middleware**: Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope

**Method Parameters**:
- `request`: `Fruitcake\LaravelDebugbar\Requests\QueriesExplainRequest`
- `debugbar`: `Fruitcake\LaravelDebugbar\LaravelDebugbar`
- `explain`: `Fruitcake\LaravelDebugbar\Support\Explain`


## AssetController

**Controller**: `Fruitcake\LaravelDebugbar\Controllers\AssetController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `_debugbar/assets` | debugbar.assets | Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope |

### getAssets

**Route**: `debugbar.assets`

**URI**: `_debugbar/assets`

**Methods**: GET

**Middleware**: Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled, Fruitcake\LaravelDebugbar\Middleware\StopRecordingTelescope

**Method Parameters**:
- `request`: `Fruitcake\LaravelDebugbar\Requests\AssetRequest`
- `assetHandler`: `DebugBar\AssetHandler`
- `debugbar`: `Fruitcake\LaravelDebugbar\LaravelDebugbar`


## ConversationController

**Controller**: `App\Http\Controllers\Api\ConversationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `api/conversations` | conversations.index | api, auth:sanctum, throttle:30,1 |
| GET | `api/conversations/{id}` | conversations.show | api, auth:sanctum, throttle:30,1 |
| PATCH | `api/conversations/{id}` | conversations.update | api, auth:sanctum, throttle:30,1 |
| DELETE | `api/conversations/{id}` | conversations.destroy | api, auth:sanctum, throttle:30,1 |

### index

**Route**: `conversations.index`

**URI**: `api/conversations`

**Methods**: GET

**Middleware**: api, auth:sanctum, throttle:30,1

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### show

**Route**: `conversations.show`

**URI**: `api/conversations/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: api, auth:sanctum, throttle:30,1

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `id`: `string`

### update

**Route**: `conversations.update`

**URI**: `api/conversations/{id}`

**Methods**: PATCH

**Parameters**:
- `id`

**Middleware**: api, auth:sanctum, throttle:30,1

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `id`: `string`

### destroy

**Route**: `conversations.destroy`

**URI**: `api/conversations/{id}`

**Methods**: DELETE

**Parameters**:
- `id`

**Middleware**: api, auth:sanctum, throttle:30,1

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `id`: `string`


## UserController

**Controller**: `App\Http\Controllers\Api\V1\UserController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `api/v1/users` | api.v1.users.index | api, throttle:60,1, auth:sanctum |
| POST | `api/v1/users/batch` | api.v1.users.batch | api, throttle:60,1, auth:sanctum |
| POST | `api/v1/users/search` | api.v1.users.search | api, throttle:60,1, auth:sanctum |
| GET | `api/v1/users/{user}` | api.v1.users.show | api, throttle:60,1, auth:sanctum |
| POST | `api/v1/users` | api.v1.users.store | api, throttle:60,1, auth:sanctum |
| PUT, PATCH | `api/v1/users/{user}` | api.v1.users.update | api, throttle:60,1, auth:sanctum |
| DELETE | `api/v1/users/{user}` | api.v1.users.destroy | api, throttle:60,1, auth:sanctum |

### index

**Route**: `api.v1.users.index`

**URI**: `api/v1/users`

**Methods**: GET

**Middleware**: api, throttle:60,1, auth:sanctum, feature:api_access

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### batch

**Route**: `api.v1.users.batch`

**URI**: `api/v1/users/batch`

**Methods**: POST

**Middleware**: api, throttle:60,1, auth:sanctum, feature:api_access

**Method Parameters**:
- `request`: `App\Http\Requests\Api\V1\BatchUserRequest`
- `createUser`: `App\Actions\CreateUser`
- `updateUser`: `App\Actions\UpdateUser`
- `deleteUser`: `App\Actions\DeleteUser`

### search

**Route**: `api.v1.users.search`

**URI**: `api/v1/users/search`

**Methods**: POST

**Middleware**: api, throttle:60,1, auth:sanctum, feature:api_access

**Method Parameters**:
- `request`: `App\Http\Requests\Api\V1\SearchUserRequest`

### show

**Route**: `api.v1.users.show`

**URI**: `api/v1/users/{user}`

**Methods**: GET

**Parameters**:
- `user`

**Middleware**: api, throttle:60,1, auth:sanctum, feature:api_access

**Method Parameters**:
- `user`: `App\Models\User`

### store

**Route**: `api.v1.users.store`

**URI**: `api/v1/users`

**Methods**: POST

**Middleware**: api, throttle:60,1, auth:sanctum, feature:api_access

**Method Parameters**:
- `request`: `App\Http\Requests\CreateUserRequest`
- `action`: `App\Actions\CreateUser`

### update

**Route**: `api.v1.users.update`

**URI**: `api/v1/users/{user}`

**Methods**: PUT, PATCH

**Parameters**:
- `user`

**Middleware**: api, throttle:60,1, auth:sanctum, feature:api_access

**Method Parameters**:
- `request`: `App\Http\Requests\UpdateUserRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\UpdateUser`

### destroy

**Route**: `api.v1.users.destroy`

**URI**: `api/v1/users/{user}`

**Methods**: DELETE

**Parameters**:
- `user`

**Middleware**: api, throttle:60,1, auth:sanctum, feature:api_access

**Method Parameters**:
- `request`: `App\Http\Requests\DeleteUserRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\DeleteUser`


## HealthController

**Controller**: `App\Http\Controllers\HealthController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `up/ready` | up.ready | web |
| GET | `up` | up | web |

### ready

**Route**: `up.ready`

**URI**: `up/ready`

**Methods**: GET

**Middleware**: web

### up

**Route**: `up`

**URI**: `up`

**Methods**: GET

**Middleware**: web


## InvitationAcceptController

**Controller**: `App\Http\Controllers\InvitationAcceptController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `invitations/{token}` | invitations.show | web |
| POST | `invitations/{token}/accept` | invitations.accept | web, auth |

### show

**Route**: `invitations.show`

**URI**: `invitations/{token}`

**Methods**: GET

**Parameters**:
- `token`

**Middleware**: web

**Method Parameters**:
- `token`: `string`

### store

**Route**: `invitations.accept`

**URI**: `invitations/{token}/accept`

**Methods**: POST

**Parameters**:
- `token`

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `token`: `string`
- `action`: `App\Actions\AcceptOrganizationInvitationAction`


## PricingController

**Controller**: `Modules\Billing\Http\Controllers\PricingController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `pricing` | pricing | web |

### index

**Route**: `pricing`

**URI**: `pricing`

**Methods**: GET

**Middleware**: web


## EnterpriseInquiryController

**Controller**: `App\Http\Controllers\EnterpriseInquiryController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `enterprise` | enterprise-inquiries.create | web |
| POST | `enterprise` | enterprise-inquiries.store | web, Spatie\Honeypot\ProtectAgainstSpam |

### create

**Route**: `enterprise-inquiries.create`

**URI**: `enterprise`

**Methods**: GET

**Middleware**: web

### store

**Route**: `enterprise-inquiries.store`

**URI**: `enterprise`

**Methods**: POST

**Middleware**: web, Spatie\Honeypot\ProtectAgainstSpam

**Method Parameters**:
- `request`: `App\Http\Requests\StoreEnterpriseInquiryRequest`
- `action`: `App\Actions\StoreEnterpriseInquiryAction`


## TermsAcceptController

**Controller**: `App\Http\Controllers\TermsAcceptController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `terms/accept` | terms.accept | web, auth, verified |
| POST | `terms/accept` | terms.accept.store | web, auth, verified |

### show

**Route**: `terms.accept`

**URI**: `terms/accept`

**Methods**: GET

**Middleware**: web, auth, verified

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `terms.accept.store`

**URI**: `terms/accept`

**Methods**: POST

**Middleware**: web, auth, verified

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## CategoriesTableController

**Controller**: `App\Http\Controllers\CategoriesTableController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `categories` | categories.table | web, auth, verified |

### index

**Route**: `categories.table`

**URI**: `categories`

**Methods**: GET

**Middleware**: web, auth, verified

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## UsersTableController

**Controller**: `App\Http\Controllers\UsersTableController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `users` | users.table | web, auth, verified |
| POST | `users/bulk-soft-delete` | users.bulk-soft-delete | web, auth, verified |
| PATCH | `users/batch-update` | users.batch-update | web, auth, verified |
| POST | `users/{user}/duplicate` | users.duplicate | web, auth, verified |
| GET | `users/{user}` | users.show | web, auth, verified |
| POST | `users/{id}/restore` | users.restore | web, auth, verified |
| DELETE | `users/{id}/force-delete` | users.force-delete | web, auth, verified |

### index

**Route**: `users.table`

**URI**: `users`

**Methods**: GET

**Middleware**: web, auth, verified

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### bulkSoftDelete

**Route**: `users.bulk-soft-delete`

**URI**: `users/bulk-soft-delete`

**Methods**: POST

**Middleware**: web, auth, verified

**Method Parameters**:
- `request`: `App\Http\Requests\BulkSoftDeleteUsersRequest`
- `action`: `App\Actions\BulkSoftDeleteUsers`

### batchUpdate

**Route**: `users.batch-update`

**URI**: `users/batch-update`

**Methods**: PATCH

**Middleware**: web, auth, verified

**Method Parameters**:
- `request`: `App\Http\Requests\BatchUpdateUsersRequest`
- `action`: `App\Actions\BatchUpdateUsersAction`

### duplicate

**Route**: `users.duplicate`

**URI**: `users/{user}/duplicate`

**Methods**: POST

**Parameters**:
- `user`

**Middleware**: web, auth, verified

**Method Parameters**:
- `user`: `App\Models\User`
- `action`: `App\Actions\DuplicateUser`
- `request`: `Illuminate\Http\Request`

### show

**Route**: `users.show`

**URI**: `users/{user}`

**Methods**: GET

**Parameters**:
- `user`

**Middleware**: web, auth, verified

**Method Parameters**:
- `user`: `App\Models\User`
- `request`: `Illuminate\Http\Request`

### restore

**Route**: `users.restore`

**URI**: `users/{id}/restore`

**Methods**: POST

**Parameters**:
- `id`

**Middleware**: web, auth, verified

**Method Parameters**:
- `id`: `string`
- `request`: `Illuminate\Http\Request`

### forceDelete

**Route**: `users.force-delete`

**URI**: `users/{id}/force-delete`

**Methods**: DELETE

**Parameters**:
- `id`

**Middleware**: web, auth, verified

**Method Parameters**:
- `id`: `string`
- `request`: `Illuminate\Http\Request`


## OrganizationsTableController

**Controller**: `App\Http\Controllers\OrganizationsTableController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `organizations/list` | organizations.list | web, auth, verified |

### index

**Route**: `organizations.list`

**URI**: `organizations/list`

**Methods**: GET

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## OrganizationController

**Controller**: `App\Http\Controllers\OrganizationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `organizations` | organizations.index | web, auth, verified |
| GET | `organizations/create` | organizations.create | web, auth, verified |
| POST | `organizations` | organizations.store | web, auth, verified |
| GET | `organizations/{organization}` | organizations.show | web, auth, verified |
| PUT, PATCH | `organizations/{organization}` | organizations.update | web, auth, verified |
| DELETE | `organizations/{organization}` | organizations.destroy | web, auth, verified |
| GET | `organizations/{organization}/edit` | organizations.edit | web, auth, verified |

### index

**Route**: `organizations.index`

**URI**: `organizations`

**Methods**: GET

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### create

**Route**: `organizations.create`

**URI**: `organizations/create`

**Methods**: GET

**Middleware**: web, auth, verified, tenancy.enabled

### store

**Route**: `organizations.store`

**URI**: `organizations`

**Methods**: POST

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `App\Http\Requests\StoreOrganizationRequest`
- `action`: `App\Actions\CreateOrganizationAction`

### show

**Route**: `organizations.show`

**URI**: `organizations/{organization}`

**Methods**: GET

**Parameters**:
- `organization`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `organization`: `App\Models\Organization`

### update

**Route**: `organizations.update`

**URI**: `organizations/{organization}`

**Methods**: PUT, PATCH

**Parameters**:
- `organization`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `App\Http\Requests\UpdateOrganizationRequest`
- `organization`: `App\Models\Organization`

### destroy

**Route**: `organizations.destroy`

**URI**: `organizations/{organization}`

**Methods**: DELETE

**Parameters**:
- `organization`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `organization`: `App\Models\Organization`

### edit

**Route**: `organizations.edit`

**URI**: `organizations/{organization}/edit`

**Methods**: GET

**Parameters**:
- `organization`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `organization`: `App\Models\Organization`


## OrganizationMemberController

**Controller**: `App\Http\Controllers\OrganizationMemberController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `organizations/{organization}/members` | organizations.members.index | web, auth, verified |
| PUT | `organizations/{organization}/members/{member}` | organizations.members.update | web, auth, verified |
| DELETE | `organizations/{organization}/members/{member}` | organizations.members.destroy | web, auth, verified |

### index

**Route**: `organizations.members.index`

**URI**: `organizations/{organization}/members`

**Methods**: GET

**Parameters**:
- `organization`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `organization`: `App\Models\Organization`

### update

**Route**: `organizations.members.update`

**URI**: `organizations/{organization}/members/{member}`

**Methods**: PUT

**Parameters**:
- `organization`
- `member`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `organization`: `App\Models\Organization`
- `member`: `App\Models\User`

### destroy

**Route**: `organizations.members.destroy`

**URI**: `organizations/{organization}/members/{member}`

**Methods**: DELETE

**Parameters**:
- `organization`
- `member`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `organization`: `App\Models\Organization`
- `member`: `App\Models\User`
- `action`: `App\Actions\RemoveOrganizationMemberAction`


## OrganizationInvitationController

**Controller**: `App\Http\Controllers\OrganizationInvitationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `organizations/{organization}/invitations` | organizations.invitations.store | web, auth, verified |
| DELETE | `organizations/{organization}/invitations/{invitation}` | organizations.invitations.destroy | web, auth, verified |
| PUT | `organizations/{organization}/invitations/{invitation}/resend` | organizations.invitations.resend | web, auth, verified |

### store

**Route**: `organizations.invitations.store`

**URI**: `organizations/{organization}/invitations`

**Methods**: POST

**Parameters**:
- `organization`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `request`: `App\Http\Requests\StoreInvitationRequest`
- `organization`: `App\Models\Organization`
- `action`: `App\Actions\InviteToOrganizationAction`

### destroy

**Route**: `organizations.invitations.destroy`

**URI**: `organizations/{organization}/invitations/{invitation}`

**Methods**: DELETE

**Parameters**:
- `organization`
- `invitation`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `organization`: `App\Models\Organization`
- `invitation`: `App\Models\OrganizationInvitation`

### update

**Route**: `organizations.invitations.resend`

**URI**: `organizations/{organization}/invitations/{invitation}/resend`

**Methods**: PUT

**Parameters**:
- `organization`
- `invitation`

**Middleware**: web, auth, verified, tenancy.enabled

**Method Parameters**:
- `organization`: `App\Models\Organization`
- `invitation`: `App\Models\OrganizationInvitation`


## BillingDashboardController

**Controller**: `Modules\Billing\Http\Controllers\BillingDashboardController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `billing` | billing.index | web, auth, verified |

### index

**Route**: `billing.index`

**URI**: `billing`

**Methods**: GET

**Middleware**: web, auth, verified, tenant


## CreditController

**Controller**: `Modules\Billing\Http\Controllers\CreditController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `billing/credits` | billing.credits.index | web, auth, verified |
| POST | `billing/credits/purchase` | billing.credits.purchase | web, auth, verified |
| POST | `billing/credits/checkout/lemon-squeezy` | billing.credits.checkout.lemon-squeezy | web, auth, verified |

### index

**Route**: `billing.credits.index`

**URI**: `billing/credits`

**Methods**: GET

**Middleware**: web, auth, verified, tenant

### purchase

**Route**: `billing.credits.purchase`

**URI**: `billing/credits/purchase`

**Methods**: POST

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### checkoutLemonSqueezy

**Route**: `billing.credits.checkout.lemon-squeezy`

**URI**: `billing/credits/checkout/lemon-squeezy`

**Methods**: POST

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## InvoiceController

**Controller**: `Modules\Billing\Http\Controllers\InvoiceController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `billing/invoices` | billing.invoices.index | web, auth, verified |
| GET | `billing/invoices/{invoice}` | billing.invoices.download | web, auth, verified |

### index

**Route**: `billing.invoices.index`

**URI**: `billing/invoices`

**Methods**: GET

**Middleware**: web, auth, verified, tenant

### download

**Route**: `billing.invoices.download`

**URI**: `billing/invoices/{invoice}`

**Methods**: GET

**Parameters**:
- `invoice`

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `invoice`: `App\Models\Billing\Invoice`


## BrandingController

**Controller**: `App\Http\Controllers\Settings\BrandingController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/branding` | settings.branding.edit | web, auth, verified |
| PUT | `settings/branding` | settings.branding.update | web, auth, verified |

### edit

**Route**: `settings.branding.edit`

**URI**: `settings/branding`

**Methods**: GET

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### update

**Route**: `settings.branding.update`

**URI**: `settings/branding`

**Methods**: PUT

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `App\Http\Requests\Settings\UpdateBrandingRequest`


## OrgFeaturesController

**Controller**: `App\Http\Controllers\Settings\OrgFeaturesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/features` | settings.features.show | web, auth, verified |
| POST | `settings/features` | settings.features.update | web, auth, verified |

### show

**Route**: `settings.features.show`

**URI**: `settings/features`

**Methods**: GET

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### update

**Route**: `settings.features.update`

**URI**: `settings/features`

**Methods**: POST

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## OrgRolesController

**Controller**: `App\Http\Controllers\Settings\OrgRolesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/roles` | settings.roles.index | web, auth, verified |
| POST | `settings/roles` | settings.roles.store | web, auth, verified |
| DELETE | `settings/roles/{role}` | settings.roles.destroy | web, auth, verified |

### index

**Route**: `settings.roles.index`

**URI**: `settings/roles`

**Methods**: GET

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `settings.roles.store`

**URI**: `settings/roles`

**Methods**: POST

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### destroy

**Route**: `settings.roles.destroy`

**URI**: `settings/roles/{role}`

**Methods**: DELETE

**Parameters**:
- `role`

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `role`: `Spatie\Permission\Models\Role`


## OrgSlugController

**Controller**: `App\Http\Controllers\Settings\OrgSlugController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/general` | settings.general.show | web, auth, verified |
| PATCH | `settings/general/slug` | settings.general.slug.update | web, auth, verified |

### show

**Route**: `settings.general.show`

**URI**: `settings/general`

**Methods**: GET

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

### update

**Route**: `settings.general.slug.update`

**URI**: `settings/general/slug`

**Methods**: PATCH

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `auditLog`: `App\Actions\RecordAuditLog`


## OrgDomainsController

**Controller**: `App\Http\Controllers\Settings\OrgDomainsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/domains` | settings.domains.show | web, auth, verified |
| POST | `settings/domains` | settings.domains.store | web, auth, verified |
| DELETE | `settings/domains/{domain}` | settings.domains.destroy | web, auth, verified |
| POST | `settings/domains/{domain}/verify` | settings.domains.verify | web, auth, verified |

### show

**Route**: `settings.domains.show`

**URI**: `settings/domains`

**Methods**: GET

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

### store

**Route**: `settings.domains.store`

**URI**: `settings/domains`

**Methods**: POST

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `auditLog`: `App\Actions\RecordAuditLog`

### destroy

**Route**: `settings.domains.destroy`

**URI**: `settings/domains/{domain}`

**Methods**: DELETE

**Parameters**:
- `domain`

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `domain`: `App\Models\OrganizationDomain`
- `auditLog`: `App\Actions\RecordAuditLog`

### verify

**Route**: `settings.domains.verify`

**URI**: `settings/domains/{domain}/verify`

**Methods**: POST

**Parameters**:
- `domain`

**Middleware**: web, auth, verified, tenant, permission:org.settings.manage

**Method Parameters**:
- `domain`: `App\Models\OrganizationDomain`


## PageController

**Controller**: `Modules\PageBuilder\Http\Controllers\PageController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `pages` | pages.index | web, auth, verified |
| GET | `pages/create` | pages.create | web, auth, verified |
| POST | `pages` | pages.store | web, auth, verified |
| GET | `pages/{page}/edit` | pages.edit | web, auth, verified |
| PUT | `pages/{page}` | pages.update | web, auth, verified |
| GET | `pages/{page}/preview` | pages.preview | web, auth, verified |
| POST | `pages/{page}/duplicate` | pages.duplicate | web, auth, verified |
| DELETE | `pages/{page}` | pages.destroy | web, auth, verified |

### index

**Route**: `pages.index`

**URI**: `pages`

**Methods**: GET

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### create

**Route**: `pages.create`

**URI**: `pages/create`

**Methods**: GET

**Middleware**: web, auth, verified, tenant

### store

**Route**: `pages.store`

**URI**: `pages`

**Methods**: POST

**Middleware**: web, auth, verified, tenant, throttle:30,1

**Method Parameters**:
- `request`: `App\Http\Requests\StorePageRequest`

### edit

**Route**: `pages.edit`

**URI**: `pages/{page}/edit`

**Methods**: GET

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `page`: `Modules\PageBuilder\Models\Page`

### update

**Route**: `pages.update`

**URI**: `pages/{page}`

**Methods**: PUT

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant, throttle:30,1

**Method Parameters**:
- `request`: `App\Http\Requests\UpdatePageRequest`
- `page`: `Modules\PageBuilder\Models\Page`

### preview

**Route**: `pages.preview`

**URI**: `pages/{page}/preview`

**Methods**: GET

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `page`: `Modules\PageBuilder\Models\Page`

### duplicate

**Route**: `pages.duplicate`

**URI**: `pages/{page}/duplicate`

**Methods**: POST

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `page`: `Modules\PageBuilder\Models\Page`

### destroy

**Route**: `pages.destroy`

**URI**: `pages/{page}`

**Methods**: DELETE

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `page`: `Modules\PageBuilder\Models\Page`


## PageViewController

**Controller**: `Modules\PageBuilder\Http\Controllers\PageViewController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `p/{slug}` | pages.show | web, auth, verified |

### show

**Route**: `pages.show`

**URI**: `p/{slug}`

**Methods**: GET

**Parameters**:
- `slug`

**Middleware**: web, auth, verified, tenant, throttle:120,1

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `slug`: `string`


## OnboardingController

**Controller**: `App\Http\Controllers\OnboardingController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `onboarding` | onboarding | web, auth, feature:onboarding |
| POST | `onboarding` | onboarding.store | web, auth, feature:onboarding |

### show

**Route**: `onboarding`

**URI**: `onboarding`

**Methods**: GET

**Middleware**: web, auth, feature:onboarding

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `onboarding.store`

**URI**: `onboarding`

**Methods**: POST

**Middleware**: web, auth, feature:onboarding

**Method Parameters**:
- `user`: `App\Models\User`
- `action`: `App\Actions\CompleteOnboardingAction`


## PersonalDataExportController

**Controller**: `Spatie\PersonalDataExport\Http\Controllers\PersonalDataExportController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `personal-data-exports/{zipFilename}` | personal-data-exports | web, auth, Spatie\PersonalDataExport\Http\Middleware\EnsureAuthorizedToDownload |

### export

**Route**: `personal-data-exports`

**URI**: `personal-data-exports/{zipFilename}`

**Methods**: GET

**Parameters**:
- `zipFilename`

**Middleware**: web, auth, Spatie\PersonalDataExport\Http\Middleware\EnsureAuthorizedToDownload, Spatie\PersonalDataExport\Http\Middleware\FiresPersonalDataExportDownloadedEvent

**Method Parameters**:
- `zipFilename`: `string`


## UserController

**Controller**: `App\Http\Controllers\UserController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| DELETE | `user` | user.destroy | web, auth |
| GET | `register` | register | web, guest, registration.enabled |
| POST | `register` | register.store | web, guest, registration.enabled |

### destroy

**Route**: `user.destroy`

**URI**: `user`

**Methods**: DELETE

**Middleware**: web, auth

**Method Parameters**:
- `request`: `App\Http\Requests\DeleteUserRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\DeleteUser`

### create

**Route**: `register`

**URI**: `register`

**Methods**: GET

**Middleware**: web, guest, registration.enabled

### store

**Route**: `register.store`

**URI**: `register`

**Methods**: POST

**Middleware**: web, guest, registration.enabled, Spatie\Honeypot\ProtectAgainstSpam, throttle:registration

**Method Parameters**:
- `request`: `App\Http\Requests\CreateUserRequest`
- `action`: `App\Actions\CreateUser`


## UserPreferencesController

**Controller**: `App\Http\Controllers\UserPreferencesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| PATCH | `user/preferences` | user.preferences.update | web, auth |

### update

**Route**: `user.preferences.update`

**URI**: `user/preferences`

**Methods**: PATCH

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `user`: `App\Models\User`
- `action`: `App\Actions\UpdateUserThemeMode`


## OrgThemeController

**Controller**: `App\Http\Controllers\OrgThemeController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `org/theme` | org.theme.save | web, auth |
| DELETE | `org/theme` | org.theme.reset | web, auth |
| POST | `org/theme/analyze-logo` | org.theme.analyze-logo | web, auth |

### save

**Route**: `org.theme.save`

**URI**: `org/theme`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### reset

**Route**: `org.theme.reset`

**URI**: `org/theme`

**Methods**: DELETE

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### analyzeLogo

**Route**: `org.theme.analyze-logo`

**URI**: `org/theme/analyze-logo`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `action`: `App\Actions\SuggestThemeFromLogo`


## UserProfileController

**Controller**: `App\Http\Controllers\UserProfileController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/profile` | user-profile.edit | web, auth |
| PATCH | `settings/profile` | user-profile.update | web, auth |

### edit

**Route**: `user-profile.edit`

**URI**: `settings/profile`

**Methods**: GET

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### update

**Route**: `user-profile.update`

**URI**: `settings/profile`

**Methods**: PATCH

**Middleware**: web, auth

**Method Parameters**:
- `request`: `App\Http\Requests\UpdateUserRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\UpdateUser`


## UserPasswordController

**Controller**: `App\Http\Controllers\UserPasswordController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/password` | password.edit | web, auth |
| PUT | `settings/password` | password.update | web, auth, throttle:6,1 |
| GET | `reset-password/{token}` | password.reset | web, guest |
| POST | `reset-password` | password.store | web, guest, throttle:password-reset-submit |

### edit

**Route**: `password.edit`

**URI**: `settings/password`

**Methods**: GET

**Middleware**: web, auth

### update

**Route**: `password.update`

**URI**: `settings/password`

**Methods**: PUT

**Middleware**: web, auth, throttle:6,1

**Method Parameters**:
- `request`: `App\Http\Requests\UpdateUserPasswordRequest`
- `user`: `App\Models\User`
- `action`: `App\Actions\UpdateUserPassword`

### create

**Route**: `password.reset`

**URI**: `reset-password/{token}`

**Methods**: GET

**Parameters**:
- `token`

**Middleware**: web, guest

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `password.store`

**URI**: `reset-password`

**Methods**: POST

**Middleware**: web, guest, throttle:password-reset-submit

**Method Parameters**:
- `request`: `App\Http\Requests\CreateUserPasswordRequest`
- `action`: `App\Actions\CreateUserPassword`


## UserTwoFactorAuthenticationController

**Controller**: `App\Http\Controllers\UserTwoFactorAuthenticationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/two-factor` | two-factor.show | web, auth, feature:two_factor_auth |

### show

**Route**: `two-factor.show`

**URI**: `settings/two-factor`

**Methods**: GET

**Middleware**: web, auth, feature:two_factor_auth, password.confirm

**Method Parameters**:
- `request`: `App\Http\Requests\ShowUserTwoFactorAuthenticationRequest`
- `user`: `App\Models\User`


## NotificationPreferencesController

**Controller**: `App\Http\Controllers\Settings\NotificationPreferencesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/notifications` | settings.notifications.show | web, auth |
| PATCH | `settings/notifications` | settings.notifications.update | web, auth |

### show

**Route**: `settings.notifications.show`

**URI**: `settings/notifications`

**Methods**: GET

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### update

**Route**: `settings.notifications.update`

**URI**: `settings/notifications`

**Methods**: PATCH

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## UserEmailResetNotificationController

**Controller**: `App\Http\Controllers\UserEmailResetNotificationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `forgot-password` | password.request | web, guest |
| POST | `forgot-password` | password.email | web, guest, throttle:password-reset-request |

### create

**Route**: `password.request`

**URI**: `forgot-password`

**Methods**: GET

**Middleware**: web, guest

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `password.email`

**URI**: `forgot-password`

**Methods**: POST

**Middleware**: web, guest, throttle:password-reset-request

**Method Parameters**:
- `request`: `App\Http\Requests\CreateUserEmailResetNotificationRequest`
- `action`: `App\Actions\CreateUserEmailResetNotification`


## SessionController

**Controller**: `App\Http\Controllers\SessionController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `login` | login | web, guest |
| POST | `login` | login.store | web, guest, throttle:login |
| POST | `logout` | logout | web, auth |

### create

**Route**: `login`

**URI**: `login`

**Methods**: GET

**Middleware**: web, guest

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `login.store`

**URI**: `login`

**Methods**: POST

**Middleware**: web, guest, throttle:login

**Method Parameters**:
- `request`: `App\Http\Requests\CreateSessionRequest`

### destroy

**Route**: `logout`

**URI**: `logout`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## SocialAuthController

**Controller**: `App\Http\Controllers\SocialAuthController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `auth/{provider}/redirect` | auth.social.redirect | web |
| GET | `auth/{provider}/callback` | auth.social.callback | web |

### redirect

**Route**: `auth.social.redirect`

**URI**: `auth/{provider}/redirect`

**Methods**: GET

**Parameters**:
- `provider`

**Middleware**: web

**Method Parameters**:
- `provider`: `string`

### callback

**Route**: `auth.social.callback`

**URI**: `auth/{provider}/callback`

**Methods**: GET

**Parameters**:
- `provider`

**Middleware**: web

**Method Parameters**:
- `provider`: `string`
- `action`: `App\Actions\FindOrCreateSocialUser`


## UserEmailVerificationNotificationController

**Controller**: `App\Http\Controllers\UserEmailVerificationNotificationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `verify-email` | verification.notice | web, auth |
| POST | `email/verification-notification` | verification.send | web, auth, throttle:6,1 |

### create

**Route**: `verification.notice`

**URI**: `verify-email`

**Methods**: GET

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `user`: `App\Models\User`

### store

**Route**: `verification.send`

**URI**: `email/verification-notification`

**Methods**: POST

**Middleware**: web, auth, throttle:6,1

**Method Parameters**:
- `user`: `App\Models\User`
- `action`: `App\Actions\CreateUserEmailVerificationNotification`


## UserEmailVerificationController

**Controller**: `App\Http\Controllers\UserEmailVerificationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `verify-email/{id}/{hash}` | verification.verify | web, auth, signed |

### update

**Route**: `verification.verify`

**URI**: `verify-email/{id}/{hash}`

**Methods**: GET

**Parameters**:
- `id`
- `hash`

**Middleware**: web, auth, signed, throttle:6,1

**Method Parameters**:
- `request`: `Illuminate\Foundation\Auth\EmailVerificationRequest`
- `user`: `App\Models\User`


## UserCan

**Controller**: `GeneaLabs\LaravelGovernor\Http\Controllers\Api\UserCan`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `api/genealabs/laravel-governor/user-can/{ability}` | genealabs.laravel-governor.api.user-can.show | auth:api, bindings, auth |

### show

**Route**: `genealabs.laravel-governor.api.user-can.show`

**URI**: `api/genealabs/laravel-governor/user-can/{ability}`

**Methods**: GET

**Parameters**:
- `ability`

**Middleware**: auth:api, bindings, auth

**Method Parameters**:
- `request`: `GeneaLabs\LaravelGovernor\Http\Requests\UserCan`
- `ability`: `string`


## UserIs

**Controller**: `GeneaLabs\LaravelGovernor\Http\Controllers\Api\UserIs`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `api/genealabs/laravel-governor/user-is/{role}` | genealabs.laravel-governor.api.user-is.show | auth:api, bindings, auth |

### show

**Route**: `genealabs.laravel-governor.api.user-is.show`

**URI**: `api/genealabs/laravel-governor/user-is/{role}`

**Methods**: GET

**Parameters**:
- `role`

**Middleware**: auth:api, bindings, auth

**Method Parameters**:
- `request`: `GeneaLabs\LaravelGovernor\Http\Requests\UserIs`
- `ability`: `string`


## RolesController

**Controller**: `GeneaLabs\LaravelGovernor\Http\Controllers\RolesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `genealabs/laravel-governor/roles` | genealabs.laravel-governor.roles.index | web, auth |
| GET | `genealabs/laravel-governor/roles/create` | genealabs.laravel-governor.roles.create | web, auth |
| POST | `genealabs/laravel-governor/roles` | genealabs.laravel-governor.roles.store | web, auth |
| GET | `genealabs/laravel-governor/roles/{role}` | genealabs.laravel-governor.roles.show | web, auth |
| GET | `genealabs/laravel-governor/roles/{role}/edit` | genealabs.laravel-governor.roles.edit | web, auth |
| PUT, PATCH | `genealabs/laravel-governor/roles/{role}` | genealabs.laravel-governor.roles.update | web, auth |
| DELETE | `genealabs/laravel-governor/roles/{role}` | genealabs.laravel-governor.roles.destroy | web, auth |

### index

**Route**: `genealabs.laravel-governor.roles.index`

**URI**: `genealabs/laravel-governor/roles`

**Methods**: GET

**Middleware**: web, auth

### create

**Route**: `genealabs.laravel-governor.roles.create`

**URI**: `genealabs/laravel-governor/roles/create`

**Methods**: GET

**Middleware**: web, auth

### store

**Route**: `genealabs.laravel-governor.roles.store`

**URI**: `genealabs/laravel-governor/roles`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `GeneaLabs\LaravelGovernor\Http\Requests\CreateRoleRequest`

### show

**Route**: `genealabs.laravel-governor.roles.show`

**URI**: `genealabs/laravel-governor/roles/{role}`

**Methods**: GET

**Parameters**:
- `role`

**Middleware**: web, auth

### edit

**Route**: `genealabs.laravel-governor.roles.edit`

**URI**: `genealabs/laravel-governor/roles/{role}/edit`

**Methods**: GET

**Parameters**:
- `role`

**Middleware**: web, auth

**Method Parameters**:
- `role`: `GeneaLabs\LaravelGovernor\Role`

### update

**Route**: `genealabs.laravel-governor.roles.update`

**URI**: `genealabs/laravel-governor/roles/{role}`

**Methods**: PUT, PATCH

**Parameters**:
- `role`

**Middleware**: web, auth

**Method Parameters**:
- `request`: `GeneaLabs\LaravelGovernor\Http\Requests\UpdateRoleRequest`

### destroy

**Route**: `genealabs.laravel-governor.roles.destroy`

**URI**: `genealabs/laravel-governor/roles/{role}`

**Methods**: DELETE

**Parameters**:
- `role`

**Middleware**: web, auth

**Method Parameters**:
- `role`: `GeneaLabs\LaravelGovernor\Role`


## GroupsController

**Controller**: `GeneaLabs\LaravelGovernor\Http\Controllers\GroupsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `genealabs/laravel-governor/groups` | genealabs.laravel-governor.groups.index | web, auth |
| GET | `genealabs/laravel-governor/groups/create` | genealabs.laravel-governor.groups.create | web, auth |
| POST | `genealabs/laravel-governor/groups` | genealabs.laravel-governor.groups.store | web, auth |
| GET | `genealabs/laravel-governor/groups/{group}` | genealabs.laravel-governor.groups.show | web, auth |
| GET | `genealabs/laravel-governor/groups/{group}/edit` | genealabs.laravel-governor.groups.edit | web, auth |
| PUT, PATCH | `genealabs/laravel-governor/groups/{group}` | genealabs.laravel-governor.groups.update | web, auth |
| DELETE | `genealabs/laravel-governor/groups/{group}` | genealabs.laravel-governor.groups.destroy | web, auth |

### index

**Route**: `genealabs.laravel-governor.groups.index`

**URI**: `genealabs/laravel-governor/groups`

**Methods**: GET

**Middleware**: web, auth

### create

**Route**: `genealabs.laravel-governor.groups.create`

**URI**: `genealabs/laravel-governor/groups/create`

**Methods**: GET

**Middleware**: web, auth

### store

**Route**: `genealabs.laravel-governor.groups.store`

**URI**: `genealabs/laravel-governor/groups`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `GeneaLabs\LaravelGovernor\Http\Requests\StoreGroupRequest`

### show

**Route**: `genealabs.laravel-governor.groups.show`

**URI**: `genealabs/laravel-governor/groups/{group}`

**Methods**: GET

**Parameters**:
- `group`

**Middleware**: web, auth

### edit

**Route**: `genealabs.laravel-governor.groups.edit`

**URI**: `genealabs/laravel-governor/groups/{group}/edit`

**Methods**: GET

**Parameters**:
- `group`

**Middleware**: web, auth

**Method Parameters**:
- `group`: `GeneaLabs\LaravelGovernor\Group`

### update

**Route**: `genealabs.laravel-governor.groups.update`

**URI**: `genealabs/laravel-governor/groups/{group}`

**Methods**: PUT, PATCH

**Parameters**:
- `group`

**Middleware**: web, auth

### destroy

**Route**: `genealabs.laravel-governor.groups.destroy`

**URI**: `genealabs/laravel-governor/groups/{group}`

**Methods**: DELETE

**Parameters**:
- `group`

**Middleware**: web, auth

**Method Parameters**:
- `request`: `GeneaLabs\LaravelGovernor\Http\Requests\GroupDeleteRequest`
- `group`: `GeneaLabs\LaravelGovernor\Group`


## TeamsController

**Controller**: `GeneaLabs\LaravelGovernor\Http\Controllers\TeamsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `genealabs/laravel-governor/teams` | genealabs.laravel-governor.teams.index | web, auth |
| GET | `genealabs/laravel-governor/teams/create` | genealabs.laravel-governor.teams.create | web, auth |
| POST | `genealabs/laravel-governor/teams` | genealabs.laravel-governor.teams.store | web, auth |
| GET | `genealabs/laravel-governor/teams/{team}` | genealabs.laravel-governor.teams.show | web, auth |
| GET | `genealabs/laravel-governor/teams/{team}/edit` | genealabs.laravel-governor.teams.edit | web, auth |
| PUT, PATCH | `genealabs/laravel-governor/teams/{team}` | genealabs.laravel-governor.teams.update | web, auth |
| DELETE | `genealabs/laravel-governor/teams/{team}` | genealabs.laravel-governor.teams.destroy | web, auth |

### index

**Route**: `genealabs.laravel-governor.teams.index`

**URI**: `genealabs/laravel-governor/teams`

**Methods**: GET

**Middleware**: web, auth

### create

**Route**: `genealabs.laravel-governor.teams.create`

**URI**: `genealabs/laravel-governor/teams/create`

**Methods**: GET

**Middleware**: web, auth

### store

**Route**: `genealabs.laravel-governor.teams.store`

**URI**: `genealabs/laravel-governor/teams`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `GeneaLabs\LaravelGovernor\Http\Requests\TeamStoreRequest`

### show

**Route**: `genealabs.laravel-governor.teams.show`

**URI**: `genealabs/laravel-governor/teams/{team}`

**Methods**: GET

**Parameters**:
- `team`

**Middleware**: web, auth

### edit

**Route**: `genealabs.laravel-governor.teams.edit`

**URI**: `genealabs/laravel-governor/teams/{team}/edit`

**Methods**: GET

**Parameters**:
- `team`

**Middleware**: web, auth

**Method Parameters**:
- `team`: `GeneaLabs\LaravelGovernor\Team`

### update

**Route**: `genealabs.laravel-governor.teams.update`

**URI**: `genealabs/laravel-governor/teams/{team}`

**Methods**: PUT, PATCH

**Parameters**:
- `team`

**Middleware**: web, auth

### destroy

**Route**: `genealabs.laravel-governor.teams.destroy`

**URI**: `genealabs/laravel-governor/teams/{team}`

**Methods**: DELETE

**Parameters**:
- `team`

**Middleware**: web, auth


## AssignmentsController

**Controller**: `GeneaLabs\LaravelGovernor\Http\Controllers\AssignmentsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `genealabs/laravel-governor/assignments` | genealabs.laravel-governor.assignments.index | web, auth |
| GET | `genealabs/laravel-governor/assignments/create` | genealabs.laravel-governor.assignments.create | web, auth |
| POST | `genealabs/laravel-governor/assignments` | genealabs.laravel-governor.assignments.store | web, auth |
| GET | `genealabs/laravel-governor/assignments/{assignment}` | genealabs.laravel-governor.assignments.show | web, auth |
| GET | `genealabs/laravel-governor/assignments/{assignment}/edit` | genealabs.laravel-governor.assignments.edit | web, auth |
| PUT, PATCH | `genealabs/laravel-governor/assignments/{assignment}` | genealabs.laravel-governor.assignments.update | web, auth |
| DELETE | `genealabs/laravel-governor/assignments/{assignment}` | genealabs.laravel-governor.assignments.destroy | web, auth |

### index

**Route**: `genealabs.laravel-governor.assignments.index`

**URI**: `genealabs/laravel-governor/assignments`

**Methods**: GET

**Middleware**: web, auth

### create

**Route**: `genealabs.laravel-governor.assignments.create`

**URI**: `genealabs/laravel-governor/assignments/create`

**Methods**: GET

**Middleware**: web, auth

### store

**Route**: `genealabs.laravel-governor.assignments.store`

**URI**: `genealabs/laravel-governor/assignments`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `GeneaLabs\LaravelGovernor\Http\Requests\CreateAssignmentRequest`

### show

**Route**: `genealabs.laravel-governor.assignments.show`

**URI**: `genealabs/laravel-governor/assignments/{assignment}`

**Methods**: GET

**Parameters**:
- `assignment`

**Middleware**: web, auth

### edit

**Route**: `genealabs.laravel-governor.assignments.edit`

**URI**: `genealabs/laravel-governor/assignments/{assignment}/edit`

**Methods**: GET

**Parameters**:
- `assignment`

**Middleware**: web, auth

### update

**Route**: `genealabs.laravel-governor.assignments.update`

**URI**: `genealabs/laravel-governor/assignments/{assignment}`

**Methods**: PUT, PATCH

**Parameters**:
- `assignment`

**Middleware**: web, auth

### destroy

**Route**: `genealabs.laravel-governor.assignments.destroy`

**URI**: `genealabs/laravel-governor/assignments/{assignment}`

**Methods**: DELETE

**Parameters**:
- `assignment`

**Middleware**: web, auth


## InvitationController

**Controller**: `GeneaLabs\LaravelGovernor\Http\Controllers\InvitationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `genealabs/laravel-governor/invitations` | genealabs.laravel-governor.invitations.index | web, auth |
| GET | `genealabs/laravel-governor/invitations/create` | genealabs.laravel-governor.invitations.create | web, auth |
| POST | `genealabs/laravel-governor/invitations` | genealabs.laravel-governor.invitations.store | web, auth |
| GET | `genealabs/laravel-governor/invitations/{invitation}` | genealabs.laravel-governor.invitations.show | web, auth |
| GET | `genealabs/laravel-governor/invitations/{invitation}/edit` | genealabs.laravel-governor.invitations.edit | web, auth |
| PUT, PATCH | `genealabs/laravel-governor/invitations/{invitation}` | genealabs.laravel-governor.invitations.update | web, auth |
| DELETE | `genealabs/laravel-governor/invitations/{invitation}` | genealabs.laravel-governor.invitations.destroy | web, auth |

### index

**Route**: `genealabs.laravel-governor.invitations.index`

**URI**: `genealabs/laravel-governor/invitations`

**Methods**: GET

**Middleware**: web, auth

### create

**Route**: `genealabs.laravel-governor.invitations.create`

**URI**: `genealabs/laravel-governor/invitations/create`

**Methods**: GET

**Middleware**: web, auth

### store

**Route**: `genealabs.laravel-governor.invitations.store`

**URI**: `genealabs/laravel-governor/invitations`

**Methods**: POST

**Middleware**: web, auth

### show

**Route**: `genealabs.laravel-governor.invitations.show`

**URI**: `genealabs/laravel-governor/invitations/{invitation}`

**Methods**: GET

**Parameters**:
- `invitation`

**Middleware**: web, auth

**Method Parameters**:
- `token`: `string`

### edit

**Route**: `genealabs.laravel-governor.invitations.edit`

**URI**: `genealabs/laravel-governor/invitations/{invitation}/edit`

**Methods**: GET

**Parameters**:
- `invitation`

**Middleware**: web, auth

### update

**Route**: `genealabs.laravel-governor.invitations.update`

**URI**: `genealabs/laravel-governor/invitations/{invitation}`

**Methods**: PUT, PATCH

**Parameters**:
- `invitation`

**Middleware**: web, auth

### destroy

**Route**: `genealabs.laravel-governor.invitations.destroy`

**URI**: `genealabs/laravel-governor/invitations/{invitation}`

**Methods**: DELETE

**Parameters**:
- `invitation`

**Middleware**: web, auth


## ReferralController

**Controller**: `Jijunair\LaravelReferral\Controllers\ReferralController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `save20/{referralCode}` | referralLink | web |
| GET | `generate-ref-accounts` | generateReferralCodes | web |

### assignReferrer

**Route**: `referralLink`

**URI**: `save20/{referralCode}`

**Methods**: GET

**Parameters**:
- `referralCode`

**Middleware**: web

**Method Parameters**:
- `referralCode`: `mixed`

### createReferralCodeForExistingUsers

**Route**: `generateReferralCodes`

**URI**: `generate-ref-accounts`

**Methods**: GET

**Middleware**: web


## DashboardStatsController

**Controller**: `Waterline\Http\Controllers\DashboardStatsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `waterline/api/stats` | waterline.stats.index | web, Waterline\Http\Middleware\Authenticate |

### index

**Route**: `waterline.stats.index`

**URI**: `waterline/api/stats`

**Methods**: GET

**Middleware**: web, Waterline\Http\Middleware\Authenticate

**Method Parameters**:
- `repository`: `Waterline\Repositories\Workflow\Interfaces\WorkflowRepositoryInterface`


## WorkflowsController

**Controller**: `Waterline\Http\Controllers\WorkflowsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `waterline/api/flows/completed` | waterline.completed | web, Waterline\Http\Middleware\Authenticate |
| GET | `waterline/api/flows/failed` | waterline.failed | web, Waterline\Http\Middleware\Authenticate |
| GET | `waterline/api/flows/running` | waterline.running | web, Waterline\Http\Middleware\Authenticate |
| GET | `waterline/api/flows/{id}` | waterline.show | web, Waterline\Http\Middleware\Authenticate |

### completed

**Route**: `waterline.completed`

**URI**: `waterline/api/flows/completed`

**Methods**: GET

**Middleware**: web, Waterline\Http\Middleware\Authenticate

### failed

**Route**: `waterline.failed`

**URI**: `waterline/api/flows/failed`

**Methods**: GET

**Middleware**: web, Waterline\Http\Middleware\Authenticate

### running

**Route**: `waterline.running`

**URI**: `waterline/api/flows/running`

**Methods**: GET

**Middleware**: web, Waterline\Http\Middleware\Authenticate

### show

**Route**: `waterline.show`

**URI**: `waterline/api/flows/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: web, Waterline\Http\Middleware\Authenticate

**Method Parameters**:
- `id`: `mixed`


## DashboardController

**Controller**: `Waterline\Http\Controllers\DashboardController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `waterline/{view?}` | waterline.index | web, Waterline\Http\Middleware\Authenticate |

### index

**Route**: `waterline.index`

**URI**: `waterline/{view?}`

**Methods**: GET

**Middleware**: web, Waterline\Http\Middleware\Authenticate


## ConfirmablePasswordController

**Controller**: `Laravel\Fortify\Http\Controllers\ConfirmablePasswordController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/confirm-password` | password.confirm | web, auth:web |
| POST | `user/confirm-password` | password.confirm.store | web, auth:web |

### show

**Route**: `password.confirm`

**URI**: `user/confirm-password`

**Methods**: GET

**Middleware**: web, auth:web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `password.confirm.store`

**URI**: `user/confirm-password`

**Methods**: POST

**Middleware**: web, auth:web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## ConfirmedPasswordStatusController

**Controller**: `Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/confirmed-password-status` | password.confirmation | web, auth:web |

### show

**Route**: `password.confirmation`

**URI**: `user/confirmed-password-status`

**Methods**: GET

**Middleware**: web, auth:web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## TwoFactorAuthenticatedSessionController

**Controller**: `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `two-factor-challenge` | two-factor.login | web, guest:web |
| POST | `two-factor-challenge` | two-factor.login.store | web, guest:web, throttle:two-factor |

### create

**Route**: `two-factor.login`

**URI**: `two-factor-challenge`

**Methods**: GET

**Middleware**: web, guest:web

**Method Parameters**:
- `request`: `Laravel\Fortify\Http\Requests\TwoFactorLoginRequest`

### store

**Route**: `two-factor.login.store`

**URI**: `two-factor-challenge`

**Methods**: POST

**Middleware**: web, guest:web, throttle:two-factor

**Method Parameters**:
- `request`: `Laravel\Fortify\Http\Requests\TwoFactorLoginRequest`


## TwoFactorAuthenticationController

**Controller**: `Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `user/two-factor-authentication` | two-factor.enable | web, auth:web, password.confirm |
| DELETE | `user/two-factor-authentication` | two-factor.disable | web, auth:web, password.confirm |

### store

**Route**: `two-factor.enable`

**URI**: `user/two-factor-authentication`

**Methods**: POST

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `enable`: `Laravel\Fortify\Actions\EnableTwoFactorAuthentication`

### destroy

**Route**: `two-factor.disable`

**URI**: `user/two-factor-authentication`

**Methods**: DELETE

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `disable`: `Laravel\Fortify\Actions\DisableTwoFactorAuthentication`


## ConfirmedTwoFactorAuthenticationController

**Controller**: `Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `user/confirmed-two-factor-authentication` | two-factor.confirm | web, auth:web, password.confirm |

### store

**Route**: `two-factor.confirm`

**URI**: `user/confirmed-two-factor-authentication`

**Methods**: POST

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `confirm`: `Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication`


## TwoFactorQrCodeController

**Controller**: `Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/two-factor-qr-code` | two-factor.qr-code | web, auth:web, password.confirm |

### show

**Route**: `two-factor.qr-code`

**URI**: `user/two-factor-qr-code`

**Methods**: GET

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## TwoFactorSecretKeyController

**Controller**: `Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/two-factor-secret-key` | two-factor.secret-key | web, auth:web, password.confirm |

### show

**Route**: `two-factor.secret-key`

**URI**: `user/two-factor-secret-key`

**Methods**: GET

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## RecoveryCodeController

**Controller**: `Laravel\Fortify\Http\Controllers\RecoveryCodeController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `user/two-factor-recovery-codes` | two-factor.recovery-codes | web, auth:web, password.confirm |
| POST | `user/two-factor-recovery-codes` | two-factor.regenerate-recovery-codes | web, auth:web, password.confirm |

### index

**Route**: `two-factor.recovery-codes`

**URI**: `user/two-factor-recovery-codes`

**Methods**: GET

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### store

**Route**: `two-factor.regenerate-recovery-codes`

**URI**: `user/two-factor-recovery-codes`

**Methods**: POST

**Middleware**: web, auth:web, password.confirm

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `generate`: `Laravel\Fortify\Actions\GenerateNewRecoveryCodes`


## DashboardStatsController

**Controller**: `Laravel\Horizon\Http\Controllers\DashboardStatsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/stats` | horizon.stats.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.stats.index`

**URI**: `horizon/api/stats`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate


## WorkloadController

**Controller**: `Laravel\Horizon\Http\Controllers\WorkloadController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/workload` | horizon.workload.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.workload.index`

**URI**: `horizon/api/workload`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `workload`: `Laravel\Horizon\Contracts\WorkloadRepository`


## MasterSupervisorController

**Controller**: `Laravel\Horizon\Http\Controllers\MasterSupervisorController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/masters` | horizon.masters.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.masters.index`

**URI**: `horizon/api/masters`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `masters`: `Laravel\Horizon\Contracts\MasterSupervisorRepository`
- `supervisors`: `Laravel\Horizon\Contracts\SupervisorRepository`


## MonitoringController

**Controller**: `Laravel\Horizon\Http\Controllers\MonitoringController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/monitoring` | horizon.monitoring.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |
| POST | `horizon/api/monitoring` | horizon.monitoring.store | horizon, Laravel\Horizon\Http\Middleware\Authenticate |
| GET | `horizon/api/monitoring/{tag}` | horizon.monitoring-tag.paginate | horizon, Laravel\Horizon\Http\Middleware\Authenticate |
| DELETE | `horizon/api/monitoring/{tag}` | horizon.monitoring-tag.destroy | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.monitoring.index`

**URI**: `horizon/api/monitoring`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

### store

**Route**: `horizon.monitoring.store`

**URI**: `horizon/api/monitoring`

**Methods**: POST

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### paginate

**Route**: `horizon.monitoring-tag.paginate`

**URI**: `horizon/api/monitoring/{tag}`

**Methods**: GET

**Parameters**:
- `tag`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### destroy

**Route**: `horizon.monitoring-tag.destroy`

**URI**: `horizon/api/monitoring/{tag}`

**Methods**: DELETE

**Parameters**:
- `tag`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `tag`: `mixed`


## JobMetricsController

**Controller**: `Laravel\Horizon\Http\Controllers\JobMetricsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/metrics/jobs` | horizon.jobs-metrics.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |
| GET | `horizon/api/metrics/jobs/{id}` | horizon.jobs-metrics.show | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.jobs-metrics.index`

**URI**: `horizon/api/metrics/jobs`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

### show

**Route**: `horizon.jobs-metrics.show`

**URI**: `horizon/api/metrics/jobs/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `id`: `mixed`


## QueueMetricsController

**Controller**: `Laravel\Horizon\Http\Controllers\QueueMetricsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/metrics/queues` | horizon.queues-metrics.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |
| GET | `horizon/api/metrics/queues/{id}` | horizon.queues-metrics.show | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.queues-metrics.index`

**URI**: `horizon/api/metrics/queues`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

### show

**Route**: `horizon.queues-metrics.show`

**URI**: `horizon/api/metrics/queues/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `id`: `mixed`


## BatchesController

**Controller**: `Laravel\Horizon\Http\Controllers\BatchesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/batches` | horizon.jobs-batches.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |
| GET | `horizon/api/batches/{id}` | horizon.jobs-batches.show | horizon, Laravel\Horizon\Http\Middleware\Authenticate |
| POST | `horizon/api/batches/retry/{id}` | horizon.jobs-batches.retry | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.jobs-batches.index`

**URI**: `horizon/api/batches`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### show

**Route**: `horizon.jobs-batches.show`

**URI**: `horizon/api/batches/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `id`: `mixed`

### retry

**Route**: `horizon.jobs-batches.retry`

**URI**: `horizon/api/batches/retry/{id}`

**Methods**: POST

**Parameters**:
- `id`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `id`: `mixed`


## PendingJobsController

**Controller**: `Laravel\Horizon\Http\Controllers\PendingJobsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/jobs/pending` | horizon.pending-jobs.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.pending-jobs.index`

**URI**: `horizon/api/jobs/pending`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## CompletedJobsController

**Controller**: `Laravel\Horizon\Http\Controllers\CompletedJobsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/jobs/completed` | horizon.completed-jobs.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.completed-jobs.index`

**URI**: `horizon/api/jobs/completed`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## SilencedJobsController

**Controller**: `Laravel\Horizon\Http\Controllers\SilencedJobsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/jobs/silenced` | horizon.silenced-jobs.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.silenced-jobs.index`

**URI**: `horizon/api/jobs/silenced`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## FailedJobsController

**Controller**: `Laravel\Horizon\Http\Controllers\FailedJobsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/jobs/failed` | horizon.failed-jobs.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |
| GET | `horizon/api/jobs/failed/{id}` | horizon.failed-jobs.show | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.failed-jobs.index`

**URI**: `horizon/api/jobs/failed`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### show

**Route**: `horizon.failed-jobs.show`

**URI**: `horizon/api/jobs/failed/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `id`: `mixed`


## RetryController

**Controller**: `Laravel\Horizon\Http\Controllers\RetryController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `horizon/api/jobs/retry/{id}` | horizon.retry-jobs.show | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### store

**Route**: `horizon.retry-jobs.show`

**URI**: `horizon/api/jobs/retry/{id}`

**Methods**: POST

**Parameters**:
- `id`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `id`: `mixed`


## JobsController

**Controller**: `Laravel\Horizon\Http\Controllers\JobsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/api/jobs/{id}` | horizon.jobs.show | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### show

**Route**: `horizon.jobs.show`

**URI**: `horizon/api/jobs/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate

**Method Parameters**:
- `id`: `mixed`


## HomeController

**Controller**: `Laravel\Horizon\Http\Controllers\HomeController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `horizon/{view?}` | horizon.index | horizon, Laravel\Horizon\Http\Middleware\Authenticate |

### index

**Route**: `horizon.index`

**URI**: `horizon/{view?}`

**Methods**: GET

**Middleware**: horizon, Laravel\Horizon\Http\Middleware\Authenticate


## CsrfCookieController

**Controller**: `Laravel\Sanctum\Http\Controllers\CsrfCookieController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `sanctum/csrf-cookie` | sanctum.csrf-cookie | web |

### show

**Route**: `sanctum.csrf-cookie`

**URI**: `sanctum/csrf-cookie`

**Methods**: GET

**Middleware**: web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## HandleRequests

**Controller**: `Livewire\Mechanisms\HandleRequests\HandleRequests`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `livewire-02dd4205/update` | default-livewire.update | web, Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders |

### handleUpdate

**Route**: `default-livewire.update`

**URI**: `livewire-02dd4205/update`

**Methods**: POST

**Middleware**: web, Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders


## FrontendAssets

**Controller**: `Livewire\Mechanisms\FrontendAssets\FrontendAssets`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `livewire-02dd4205/livewire.js` | - | - |
| GET | `livewire-02dd4205/livewire.min.js.map` | - | - |
| GET | `livewire-02dd4205/livewire.csp.min.js.map` | - | - |

### returnJavaScriptAsFile

**Route**: ``

**URI**: `livewire-02dd4205/livewire.js`

**Methods**: GET

### maps

**Route**: ``

**URI**: `livewire-02dd4205/livewire.min.js.map`

**Methods**: GET

### cspMaps

**Route**: ``

**URI**: `livewire-02dd4205/livewire.csp.min.js.map`

**Methods**: GET


## FileUploadController

**Controller**: `Livewire\Features\SupportFileUploads\FileUploadController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `livewire-02dd4205/upload-file` | livewire.upload-file | web, throttle:60,1 |

### handle

**Route**: `livewire.upload-file`

**URI**: `livewire-02dd4205/upload-file`

**Methods**: POST

**Middleware**: web, throttle:60,1


## FilePreviewController

**Controller**: `Livewire\Features\SupportFileUploads\FilePreviewController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `livewire-02dd4205/preview-file/{filename}` | livewire.preview-file | web |

### handle

**Route**: `livewire.preview-file`

**URI**: `livewire-02dd4205/preview-file/{filename}`

**Methods**: GET

**Parameters**:
- `filename`

**Middleware**: web

**Method Parameters**:
- `filename`: `mixed`


## DataTableExportController

**Controller**: `Machour\DataTable\Http\Controllers\DataTableExportController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `data-table/export-status` | data-table.export-status | web, auth |

### status

**Route**: `data-table.export-status`

**URI**: `data-table/export-status`

**Methods**: GET

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## DataTableAiController

**Controller**: `Machour\DataTable\Http\Controllers\DataTableAiController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `data-table/ai/{table}/query` | data-table.ai.query | web, auth |
| POST | `data-table/ai/{table}/insights` | data-table.ai.insights | web, auth |
| POST | `data-table/ai/{table}/column-summary` | data-table.ai.column-summary | web, auth |
| POST | `data-table/ai/{table}/suggest` | data-table.ai.suggest | web, auth |
| POST | `data-table/ai/{table}/enrich` | data-table.ai.enrich | web, auth |
| POST | `data-table/ai/{table}/visualize` | data-table.ai.visualize | web, auth |

### query

**Route**: `data-table.ai.query`

**URI**: `data-table/ai/{table}/query`

**Methods**: POST

**Parameters**:
- `table`

**Middleware**: web, auth

**Method Parameters**:
- `table`: `string`
- `request`: `Illuminate\Http\Request`

### insights

**Route**: `data-table.ai.insights`

**URI**: `data-table/ai/{table}/insights`

**Methods**: POST

**Parameters**:
- `table`

**Middleware**: web, auth

**Method Parameters**:
- `table`: `string`
- `request`: `Illuminate\Http\Request`

### columnSummary

**Route**: `data-table.ai.column-summary`

**URI**: `data-table/ai/{table}/column-summary`

**Methods**: POST

**Parameters**:
- `table`

**Middleware**: web, auth

**Method Parameters**:
- `table`: `string`
- `request`: `Illuminate\Http\Request`

### suggest

**Route**: `data-table.ai.suggest`

**URI**: `data-table/ai/{table}/suggest`

**Methods**: POST

**Parameters**:
- `table`

**Middleware**: web, auth

**Method Parameters**:
- `table`: `string`
- `request`: `Illuminate\Http\Request`

### enrich

**Route**: `data-table.ai.enrich`

**URI**: `data-table/ai/{table}/enrich`

**Methods**: POST

**Parameters**:
- `table`

**Middleware**: web, auth

**Method Parameters**:
- `table`: `string`
- `request`: `Illuminate\Http\Request`

### visualize

**Route**: `data-table.ai.visualize`

**URI**: `data-table/ai/{table}/visualize`

**Methods**: POST

**Parameters**:
- `table`

**Middleware**: web, auth

**Method Parameters**:
- `table`: `string`
- `request`: `Illuminate\Http\Request`


## SavedViewController

**Controller**: `Machour\DataTable\Http\Controllers\SavedViewController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `data-table/saved-views/{tableName}` | data-table.saved-views.index | web, auth |
| POST | `data-table/saved-views/{tableName}` | data-table.saved-views.store | web, auth |
| PUT | `data-table/saved-views/{tableName}/{viewId}` | data-table.saved-views.update | web, auth |
| DELETE | `data-table/saved-views/{tableName}/{viewId}` | data-table.saved-views.destroy | web, auth |

### index

**Route**: `data-table.saved-views.index`

**URI**: `data-table/saved-views/{tableName}`

**Methods**: GET

**Parameters**:
- `tableName`

**Middleware**: web, auth

**Method Parameters**:
- `tableName`: `string`
- `request`: `Illuminate\Http\Request`

### store

**Route**: `data-table.saved-views.store`

**URI**: `data-table/saved-views/{tableName}`

**Methods**: POST

**Parameters**:
- `tableName`

**Middleware**: web, auth

**Method Parameters**:
- `tableName`: `string`
- `request`: `Illuminate\Http\Request`

### update

**Route**: `data-table.saved-views.update`

**URI**: `data-table/saved-views/{tableName}/{viewId}`

**Methods**: PUT

**Parameters**:
- `tableName`
- `viewId`

**Middleware**: web, auth

**Method Parameters**:
- `tableName`: `string`
- `viewId`: `int`
- `request`: `Illuminate\Http\Request`

### destroy

**Route**: `data-table.saved-views.destroy`

**URI**: `data-table/saved-views/{tableName}/{viewId}`

**Methods**: DELETE

**Parameters**:
- `tableName`
- `viewId`

**Middleware**: web, auth

**Method Parameters**:
- `tableName`: `string`
- `viewId`: `int`
- `request`: `Illuminate\Http\Request`


## EventController

**Controller**: `Pan\Adapters\Laravel\Http\Controllers\EventController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `pan/events` | - | - |

### store

**Route**: ``

**URI**: `pan/events`

**Methods**: POST

**Method Parameters**:
- `request`: `Pan\Adapters\Laravel\Http\Requests\CreateEventRequest`
- `action`: `Pan\Actions\CreateEvent`


## BlogController

**Controller**: `Modules\Blog\Http\Controllers\BlogController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `blog` | blog.index | Illuminate\Routing\Middleware\SubstituteBindings, feature:blog |
| GET | `blog/{post}` | blog.show | Illuminate\Routing\Middleware\SubstituteBindings, feature:blog |

### index

**Route**: `blog.index`

**URI**: `blog`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, feature:blog

### show

**Route**: `blog.show`

**URI**: `blog/{post}`

**Methods**: GET

**Parameters**:
- `post`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, feature:blog

**Method Parameters**:
- `post`: `Modules\Blog\Models\Post`


## PostsTableController

**Controller**: `Modules\Blog\Http\Controllers\PostsTableController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `posts` | posts.table | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |

### index

**Route**: `posts.table`

**URI**: `posts`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## ChangelogController

**Controller**: `Modules\Changelog\Http\Controllers\ChangelogController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `changelog` | changelog.index | Illuminate\Routing\Middleware\SubstituteBindings, feature:changelog |

### index

**Route**: `changelog.index`

**URI**: `changelog`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, feature:changelog


## HelpCenterController

**Controller**: `Modules\Help\Http\Controllers\HelpCenterController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `help` | help.index | Illuminate\Routing\Middleware\SubstituteBindings, feature:help |
| GET | `help/{helpArticle}` | help.show | Illuminate\Routing\Middleware\SubstituteBindings, feature:help |

### index

**Route**: `help.index`

**URI**: `help`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, feature:help

### show

**Route**: `help.show`

**URI**: `help/{helpArticle}`

**Methods**: GET

**Parameters**:
- `helpArticle`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, feature:help

**Method Parameters**:
- `helpArticle`: `Modules\Help\Models\HelpArticle`


## ContactSubmissionController

**Controller**: `Modules\Contact\Http\Controllers\ContactSubmissionController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `contact` | contact.create | Illuminate\Routing\Middleware\SubstituteBindings, feature:contact |
| POST | `contact` | contact.store | Illuminate\Routing\Middleware\SubstituteBindings, feature:contact, Spatie\Honeypot\ProtectAgainstSpam |

### create

**Route**: `contact.create`

**URI**: `contact`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, feature:contact

### store

**Route**: `contact.store`

**URI**: `contact`

**Methods**: POST

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, feature:contact, Spatie\Honeypot\ProtectAgainstSpam

**Method Parameters**:
- `request`: `Modules\Contact\Http\Requests\StoreContactSubmissionRequest`
- `action`: `Modules\Contact\Actions\StoreContactSubmission`


## AnnouncementsTableController

**Controller**: `Modules\Announcements\Http\Controllers\AnnouncementsTableController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `announcements` | announcements.table | Illuminate\Routing\Middleware\SubstituteBindings, web, auth |

### index

**Route**: `announcements.table`

**URI**: `announcements`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, web, auth, verified

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## AchievementsController

**Controller**: `Modules\Gamification\Http\Controllers\AchievementsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/achievements` | achievements.show | Illuminate\Routing\Middleware\SubstituteBindings, auth, feature:gamification |

### show

**Route**: `achievements.show`

**URI**: `settings/achievements`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, feature:gamification

**Method Parameters**:
- `user`: `App\Models\User`


## ReportController

**Controller**: `Modules\Reports\Http\Controllers\ReportController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `reports` | reports.index | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| GET | `reports/create` | reports.create | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| POST | `reports` | reports.store | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| GET | `reports/{report}` | reports.show | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| GET | `reports/{report}/edit` | reports.edit | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| PUT | `reports/{report}` | reports.update | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| DELETE | `reports/{report}` | reports.destroy | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| POST | `reports/{report}/export` | reports.export | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| GET | `reports/{report}/outputs/{output}/download` | reports.outputs.download | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |

### index

**Route**: `reports.index`

**URI**: `reports`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports, can:viewAny,Modules\Reports\Models\Report

### create

**Route**: `reports.create`

**URI**: `reports/create`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports, can:create,Modules\Reports\Models\Report

### store

**Route**: `reports.store`

**URI**: `reports`

**Methods**: POST

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports, throttle:30,1, can:create,Modules\Reports\Models\Report

**Method Parameters**:
- `request`: `Modules\Reports\Http\Requests\StoreReportRequest`

### show

**Route**: `reports.show`

**URI**: `reports/{report}`

**Methods**: GET

**Parameters**:
- `report`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports, can:view,report

**Method Parameters**:
- `report`: `Modules\Reports\Models\Report`

### edit

**Route**: `reports.edit`

**URI**: `reports/{report}/edit`

**Methods**: GET

**Parameters**:
- `report`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports, can:update,report

**Method Parameters**:
- `report`: `Modules\Reports\Models\Report`

### update

**Route**: `reports.update`

**URI**: `reports/{report}`

**Methods**: PUT

**Parameters**:
- `report`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports, throttle:30,1, can:update,report

**Method Parameters**:
- `request`: `Modules\Reports\Http\Requests\UpdateReportRequest`
- `report`: `Modules\Reports\Models\Report`

### destroy

**Route**: `reports.destroy`

**URI**: `reports/{report}`

**Methods**: DELETE

**Parameters**:
- `report`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports, can:delete,report

**Method Parameters**:
- `report`: `Modules\Reports\Models\Report`

### export

**Route**: `reports.export`

**URI**: `reports/{report}/export`

**Methods**: POST

**Parameters**:
- `report`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports, throttle:10,1

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `report`: `Modules\Reports\Models\Report`

### downloadOutput

**Route**: `reports.outputs.download`

**URI**: `reports/{report}/outputs/{output}/download`

**Methods**: GET

**Parameters**:
- `report`
- `output`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:reports

**Method Parameters**:
- `report`: `Modules\Reports\Models\Report`
- `output`: `Modules\Reports\Models\ReportOutput`


## DashboardBuilderController

**Controller**: `Modules\Dashboards\Http\Controllers\DashboardBuilderController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `dashboards` | dashboards.index | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| GET | `dashboards/create` | dashboards.create | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| POST | `dashboards` | dashboards.store | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| GET | `dashboards/{dashboard}` | dashboards.show | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| GET | `dashboards/{dashboard}/edit` | dashboards.edit | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| PUT | `dashboards/{dashboard}` | dashboards.update | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| DELETE | `dashboards/{dashboard}` | dashboards.destroy | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |
| POST | `dashboards/{dashboard}/set-default` | dashboards.set-default | Illuminate\Routing\Middleware\SubstituteBindings, auth, verified |

### index

**Route**: `dashboards.index`

**URI**: `dashboards`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:dashboards, can:viewAny,Modules\Dashboards\Models\Dashboard

### create

**Route**: `dashboards.create`

**URI**: `dashboards/create`

**Methods**: GET

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:dashboards, can:create,Modules\Dashboards\Models\Dashboard

### store

**Route**: `dashboards.store`

**URI**: `dashboards`

**Methods**: POST

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:dashboards, throttle:30,1, can:create,Modules\Dashboards\Models\Dashboard

**Method Parameters**:
- `request`: `Modules\Dashboards\Http\Requests\StoreDashboardRequest`

### show

**Route**: `dashboards.show`

**URI**: `dashboards/{dashboard}`

**Methods**: GET

**Parameters**:
- `dashboard`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:dashboards, can:view,dashboard

**Method Parameters**:
- `dashboard`: `Modules\Dashboards\Models\Dashboard`

### edit

**Route**: `dashboards.edit`

**URI**: `dashboards/{dashboard}/edit`

**Methods**: GET

**Parameters**:
- `dashboard`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:dashboards, can:update,dashboard

**Method Parameters**:
- `dashboard`: `Modules\Dashboards\Models\Dashboard`

### update

**Route**: `dashboards.update`

**URI**: `dashboards/{dashboard}`

**Methods**: PUT

**Parameters**:
- `dashboard`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:dashboards, throttle:30,1, can:update,dashboard

**Method Parameters**:
- `request`: `Modules\Dashboards\Http\Requests\UpdateDashboardRequest`
- `dashboard`: `Modules\Dashboards\Models\Dashboard`

### destroy

**Route**: `dashboards.destroy`

**URI**: `dashboards/{dashboard}`

**Methods**: DELETE

**Parameters**:
- `dashboard`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:dashboards, can:delete,dashboard

**Method Parameters**:
- `dashboard`: `Modules\Dashboards\Models\Dashboard`

### setDefault

**Route**: `dashboards.set-default`

**URI**: `dashboards/{dashboard}/set-default`

**Methods**: POST

**Parameters**:
- `dashboard`

**Middleware**: Illuminate\Routing\Middleware\SubstituteBindings, auth, verified, tenant, feature:dashboards, throttle:10,1

**Method Parameters**:
- `dashboard`: `Modules\Dashboards\Models\Dashboard`


## BroadcastController

**Controller**: `\Illuminate\Broadcasting\BroadcastController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET, POST | `broadcasting/auth` | - | web |

### authenticate

**Route**: ``

**URI**: `broadcasting/auth`

**Methods**: GET, POST

**Middleware**: web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`



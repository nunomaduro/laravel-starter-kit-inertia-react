# API Reference

This document lists all available routes in the application.

**Last Updated**: 2026-03-08

## New Routes (added 2026-03-08)

### Workspace URL & Custom Domains

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/general` | `settings.general.show` | auth, tenant, org.settings.manage |
| PATCH | `settings/general/slug` | `settings.general.slug.update` | auth, tenant, org.settings.manage |
| GET | `settings/domains` | `settings.domains.show` | auth, tenant, org.settings.manage |
| POST | `settings/domains` | `settings.domains.store` | auth, tenant, org.settings.manage |
| DELETE | `settings/domains/{domain}` | `settings.domains.destroy` | auth, tenant, org.settings.manage |
| POST | `settings/domains/{domain}/verify` | `settings.domains.verify` | auth, tenant, org.settings.manage |
| GET | `api/slug-availability` | `api.slug-availability` | auth |
| GET | `internal/caddy/ask` | `internal.caddy.ask` | InternalRequestMiddleware (IP allowlist) |

### Social OAuth Login

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `auth/{provider}/redirect` | `auth.social.redirect` | — (public) |
| GET | `auth/{provider}/callback` | `auth.social.callback` | — (public) |

Supported providers: `google`, `github` (controlled by `AuthSettings::google_oauth_enabled` / `github_oauth_enabled`).

### In-App Notifications

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `notifications` | `notifications.index` | auth |
| POST | `notifications/{notification}/read` | `notifications.read` | auth |
| POST | `notifications/read-all` | `notifications.read-all` | auth |
| DELETE | `notifications/{notification}` | `notifications.delete` | auth |
| DELETE | `notifications` | `notifications.clear` | auth |

### Notification Preferences

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/notifications` | `settings.notifications.show` | auth |
| PATCH | `settings/notifications` | `settings.notifications.update` | auth |

## Closure

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `filament/exports/{export}/download` | filament.exports.download | filament.actions |
| GET | `filament/imports/{import}/failed-rows/download` | filament.imports.failed-rows.download | filament.actions |
| GET | `admin/login` | filament.admin.auth.login | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| POST | `admin/logout` | filament.admin.auth.logout | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin` | filament.admin.pages.dashboard | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/user-activities-page` | filament.admin.pages.user-activities-page | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/api-docs` | filament.admin.pages.api-docs | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/billing/revenue-analytics` | filament.admin.pages.billing.revenue-analytics | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-activity-log` | filament.admin.pages.manage-activity-log | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-ai` | filament.admin.pages.manage-ai | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-app` | filament.admin.pages.manage-app | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-auth` | filament.admin.pages.manage-auth | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-backup` | filament.admin.pages.manage-backup | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-billing` | filament.admin.pages.manage-billing | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-broadcasting` | filament.admin.pages.manage-broadcasting | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-cookie-consent` | filament.admin.pages.manage-cookie-consent | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-feature-flags` | filament.admin.pages.manage-feature-flags | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-filesystem` | filament.admin.pages.manage-filesystem | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-impersonate` | filament.admin.pages.manage-impersonate | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-integrations` | filament.admin.pages.manage-integrations | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-lemon-squeezy` | filament.admin.pages.manage-lemon-squeezy | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-mail` | filament.admin.pages.manage-mail | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-media` | filament.admin.pages.manage-media | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-memory` | filament.admin.pages.manage-memory | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-monitoring` | filament.admin.pages.manage-monitoring | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-organization-overrides` | filament.admin.pages.manage-organization-overrides | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-paddle` | filament.admin.pages.manage-paddle | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-performance` | filament.admin.pages.manage-performance | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-permissions` | filament.admin.pages.manage-permissions | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-prism` | filament.admin.pages.manage-prism | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-scout` | filament.admin.pages.manage-scout | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-security` | filament.admin.pages.manage-security | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-seo` | filament.admin.pages.manage-seo | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-stripe` | filament.admin.pages.manage-stripe | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-tenancy` | filament.admin.pages.manage-tenancy | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/manage-theme` | filament.admin.pages.manage-theme | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/analytics/product` | filament.admin.pages.analytics.product | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/setup-wizard` | filament.admin.pages.setup-wizard | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/feature-segments` | filament.admin.resources.feature-segments.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/activity-logs` | filament.admin.resources.activity-logs.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/activity-logs/{record}` | filament.admin.resources.activity-logs.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/billing/affiliates` | filament.admin.resources.billing.affiliates.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/categories` | filament.admin.resources.categories.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/changelog-entries` | filament.admin.resources.changelog-entries.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/changelog-entries/create` | filament.admin.resources.changelog-entries.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/changelog-entries/{record}` | filament.admin.resources.changelog-entries.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/changelog-entries/{record}/edit` | filament.admin.resources.changelog-entries.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/contact-submissions` | filament.admin.resources.contact-submissions.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/contact-submissions/{record}` | filament.admin.resources.contact-submissions.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/contact-submissions/{record}/edit` | filament.admin.resources.contact-submissions.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/credit-packs` | filament.admin.resources.credit-packs.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/credit-packs/create` | filament.admin.resources.credit-packs.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/credit-packs/{record}/edit` | filament.admin.resources.credit-packs.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/enterprise-inquiries` | filament.admin.resources.enterprise-inquiries.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/enterprise-inquiries/{record}` | filament.admin.resources.enterprise-inquiries.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/enterprise-inquiries/{record}/edit` | filament.admin.resources.enterprise-inquiries.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/help-articles` | filament.admin.resources.help-articles.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/help-articles/create` | filament.admin.resources.help-articles.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/help-articles/{record}` | filament.admin.resources.help-articles.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/help-articles/{record}/edit` | filament.admin.resources.help-articles.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/mail-templates` | filament.admin.resources.mail-templates.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/mail-templates/{record}/edit` | filament.admin.resources.mail-templates.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/organization-invitations` | filament.admin.resources.organization-invitations.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/organization-invitations/create` | filament.admin.resources.organization-invitations.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/organization-invitations/{record}/edit` | filament.admin.resources.organization-invitations.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/organizations` | filament.admin.resources.organizations.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/organizations/create` | filament.admin.resources.organizations.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/organizations/{record}/edit` | filament.admin.resources.organizations.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/permissions` | filament.admin.resources.permissions.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/permissions/{record}` | filament.admin.resources.permissions.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/posts` | filament.admin.resources.posts.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/posts/create` | filament.admin.resources.posts.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/posts/{record}` | filament.admin.resources.posts.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/posts/{record}/edit` | filament.admin.resources.posts.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/roles` | filament.admin.resources.roles.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/roles/create` | filament.admin.resources.roles.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/roles/{record}` | filament.admin.resources.roles.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/roles/{record}/edit` | filament.admin.resources.roles.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/terms-versions` | filament.admin.resources.terms-versions.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/terms-versions/create` | filament.admin.resources.terms-versions.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/terms-versions/{record}/edit` | filament.admin.resources.terms-versions.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/users` | filament.admin.resources.users.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/users/create` | filament.admin.resources.users.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/users/{record}` | filament.admin.resources.users.view | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/users/{record}/edit` | filament.admin.resources.users.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/vouchers` | filament.admin.resources.vouchers.index | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/vouchers/create` | filament.admin.resources.vouchers.create | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| GET | `admin/vouchers/{record}/edit` | filament.admin.resources.vouchers.edit | panel:admin, AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware, Illuminate\Cookie\Middleware\EncryptCookies |
| POST | `_boost/browser-logs` | boost.browser-logs | - |
| GET | `mcp/api` | - | - |
| DELETE | `mcp/api` | - | - |
| POST | `mcp/api` | - | Laravel\Mcp\Server\Middleware\ReorderJsonAccept, Laravel\Mcp\Server\Middleware\AddWwwAuthenticateHeader, auth:sanctum |
| POST | `lemon-squeezy/webhook` | lemon-squeezy.webhook | - |
| GET | `livewire-f0cf3e9a/js/{component}.js` | - | - |
| GET | `livewire-f0cf3e9a/css/{component}.css` | - | - |
| GET | `livewire-f0cf3e9a/css/{component}.global.css` | - | - |
| GET | `data-table/export/{table}` | data-table.export | web |
| GET | `data-table/select-all/{table}` | data-table.select-all | web |
| PATCH | `data-table/inline-edit/{table}/{id}` | data-table.inline-edit | web |
| PATCH | `data-table/toggle/{table}/{id}` | data-table.toggle | web |
| GET | `data-table/detail/{table}/{id}` | data-table.detail | web |
| GET | `data-table/filter-options/{table}/{column}` | data-table.filter-options | web |
| GET | `data-table/cascading-options/{table}/{column}` | data-table.cascading-options | web |
| PATCH | `data-table/reorder/{table}` | data-table.reorder | web |
| POST | `data-table/import/{table}` | data-table.import | web |
| GET | `filament-excel/{path}` | filament-excel-download | web, signed |
| GET | `filament-impersonate/leave` | filament-impersonate.leave | web |
| GET | `api` | api | api |
| POST | `api/chat` | api.chat | api, auth:sanctum |
| GET | `api/chat/memories` | chat.memories | api, auth:sanctum |
| GET | `api/v1` | api.v1.info | api, throttle:60,1 |
| GET | `favicon.ico` | favicon | web |
| GET | `robots.txt` | robots | web |
| GET | `up` | up | web |
| GET | `/` | home | web |
| GET | `cookie-consent/accept` | cookie-consent.accept | web, feature:cookie_consent |
| GET | `legal/terms` | legal.terms | web |
| GET | `legal/privacy` | legal.privacy | web |
| POST | `help/{helpArticle}/rate` | help.rate | web, feature:help |
| GET | `dashboard` | dashboard | web, auth, verified |
| GET | `chat` | chat | web, auth, verified |
| POST | `organizations/switch` | organizations.switch | web, auth, verified |
| GET | `search` | search | web, auth, verified |
| GET | `profile/export-pdf` | profile.export-pdf | web, auth, verified |
| POST | `webhooks/stripe` | webhooks.stripe | web |
| POST | `webhooks/paddle` | webhooks.paddle | web |
| GET, POST, PUT, PATCH, DELETE | `settings` | settings | web, auth |
| GET | `settings/appearance` | appearance.edit | web, auth, feature:appearance_settings |
| GET | `settings/personal-data-export` | personal-data-export.edit | web, auth, feature:personal_data_export |
| POST | `settings/personal-data-export` | personal-data-export.store | web, auth, feature:personal_data_export |
| GET | `storage/{path}` | storage.local | - |
| PUT | `storage/{path}` | storage.local.upload | - |
| GET | `docs/api` | scramble.docs.ui | web, Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess |
| GET | `docs/api.json` | scramble.docs.document | web, Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess |


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


## SessionController

**Controller**: `App\Http\Controllers\SessionController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `login` | login | web, guest |
| POST | `login` | login.store | web, guest |
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

**Middleware**: web, guest

**Method Parameters**:
- `request`: `App\Http\Requests\CreateSessionRequest`

### destroy

**Route**: `logout`

**URI**: `logout`

**Methods**: POST

**Middleware**: web, auth

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


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
| POST | `livewire-f0cf3e9a/update` | default-livewire.update | web, Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders |

### handleUpdate

**Route**: `default-livewire.update`

**URI**: `livewire-f0cf3e9a/update`

**Methods**: POST

**Middleware**: web, Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders


## FrontendAssets

**Controller**: `Livewire\Mechanisms\FrontendAssets\FrontendAssets`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `livewire-f0cf3e9a/livewire.js` | - | - |
| GET | `livewire-f0cf3e9a/livewire.min.js.map` | - | - |
| GET | `livewire-f0cf3e9a/livewire.csp.min.js.map` | - | - |

### returnJavaScriptAsFile

**Route**: ``

**URI**: `livewire-f0cf3e9a/livewire.js`

**Methods**: GET

### maps

**Route**: ``

**URI**: `livewire-f0cf3e9a/livewire.min.js.map`

**Methods**: GET

### cspMaps

**Route**: ``

**URI**: `livewire-f0cf3e9a/livewire.csp.min.js.map`

**Methods**: GET


## FileUploadController

**Controller**: `Livewire\Features\SupportFileUploads\FileUploadController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `livewire-f0cf3e9a/upload-file` | livewire.upload-file | web, throttle:60,1 |

### handle

**Route**: `livewire.upload-file`

**URI**: `livewire-f0cf3e9a/upload-file`

**Methods**: POST

**Middleware**: web, throttle:60,1


## FilePreviewController

**Controller**: `Livewire\Features\SupportFileUploads\FilePreviewController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `livewire-f0cf3e9a/preview-file/{filename}` | livewire.preview-file | web |

### handle

**Route**: `livewire.preview-file`

**URI**: `livewire-f0cf3e9a/preview-file/{filename}`

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
| GET | `data-table/export-status` | data-table.export-status | web |

### status

**Route**: `data-table.export-status`

**URI**: `data-table/export-status`

**Methods**: GET

**Middleware**: web

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## SavedViewController

**Controller**: `Machour\DataTable\Http\Controllers\SavedViewController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `data-table/saved-views/{tableName}` | data-table.saved-views.index | web |
| POST | `data-table/saved-views/{tableName}` | data-table.saved-views.store | web |
| PUT | `data-table/saved-views/{tableName}/{viewId}` | data-table.saved-views.update | web |
| DELETE | `data-table/saved-views/{tableName}/{viewId}` | data-table.saved-views.destroy | web |

### index

**Route**: `data-table.saved-views.index`

**URI**: `data-table/saved-views/{tableName}`

**Methods**: GET

**Parameters**:
- `tableName`

**Middleware**: web

**Method Parameters**:
- `tableName`: `string`
- `request`: `Illuminate\Http\Request`

### store

**Route**: `data-table.saved-views.store`

**URI**: `data-table/saved-views/{tableName}`

**Methods**: POST

**Parameters**:
- `tableName`

**Middleware**: web

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

**Middleware**: web

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

**Middleware**: web

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


## MailController

**Controller**: `Laravel\Telescope\Http\Controllers\MailController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/mail` | - | telescope |
| GET | `telescope/telescope-api/mail/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/mail`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/mail/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## MailHtmlController

**Controller**: `Laravel\Telescope\Http\Controllers\MailHtmlController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `telescope/telescope-api/mail/{telescopeEntryId}/preview` | - | telescope |

### show

**Route**: ``

**URI**: `telescope/telescope-api/mail/{telescopeEntryId}/preview`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## MailEmlController

**Controller**: `Laravel\Telescope\Http\Controllers\MailEmlController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `telescope/telescope-api/mail/{telescopeEntryId}/download` | - | telescope |

### show

**Route**: ``

**URI**: `telescope/telescope-api/mail/{telescopeEntryId}/download`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## ExceptionController

**Controller**: `Laravel\Telescope\Http\Controllers\ExceptionController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/exceptions` | - | telescope |
| GET | `telescope/telescope-api/exceptions/{telescopeEntryId}` | - | telescope |
| PUT | `telescope/telescope-api/exceptions/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/exceptions`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/exceptions/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`

### update

**Route**: ``

**URI**: `telescope/telescope-api/exceptions/{telescopeEntryId}`

**Methods**: PUT

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `request`: `Illuminate\Http\Request`
- `id`: `mixed`


## DumpController

**Controller**: `Laravel\Telescope\Http\Controllers\DumpController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/dumps` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/dumps`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`


## LogController

**Controller**: `Laravel\Telescope\Http\Controllers\LogController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/logs` | - | telescope |
| GET | `telescope/telescope-api/logs/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/logs`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/logs/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## NotificationsController

**Controller**: `Laravel\Telescope\Http\Controllers\NotificationsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/notifications` | - | telescope |
| GET | `telescope/telescope-api/notifications/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/notifications`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/notifications/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## QueueController

**Controller**: `Laravel\Telescope\Http\Controllers\QueueController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/jobs` | - | telescope |
| GET | `telescope/telescope-api/jobs/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/jobs`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/jobs/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## QueueBatchesController

**Controller**: `Laravel\Telescope\Http\Controllers\QueueBatchesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/batches` | - | telescope |
| GET | `telescope/telescope-api/batches/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/batches`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/batches/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## EventsController

**Controller**: `Laravel\Telescope\Http\Controllers\EventsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/events` | - | telescope |
| GET | `telescope/telescope-api/events/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/events`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/events/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## GatesController

**Controller**: `Laravel\Telescope\Http\Controllers\GatesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/gates` | - | telescope |
| GET | `telescope/telescope-api/gates/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/gates`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/gates/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## CacheController

**Controller**: `Laravel\Telescope\Http\Controllers\CacheController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/cache` | - | telescope |
| GET | `telescope/telescope-api/cache/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/cache`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/cache/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## QueriesController

**Controller**: `Laravel\Telescope\Http\Controllers\QueriesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/queries` | - | telescope |
| GET | `telescope/telescope-api/queries/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/queries`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/queries/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## ModelsController

**Controller**: `Laravel\Telescope\Http\Controllers\ModelsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/models` | - | telescope |
| GET | `telescope/telescope-api/models/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/models`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/models/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## RequestsController

**Controller**: `Laravel\Telescope\Http\Controllers\RequestsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/requests` | - | telescope |
| GET | `telescope/telescope-api/requests/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/requests`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/requests/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## ViewsController

**Controller**: `Laravel\Telescope\Http\Controllers\ViewsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/views` | - | telescope |
| GET | `telescope/telescope-api/views/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/views`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/views/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## CommandsController

**Controller**: `Laravel\Telescope\Http\Controllers\CommandsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/commands` | - | telescope |
| GET | `telescope/telescope-api/commands/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/commands`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/commands/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## ScheduleController

**Controller**: `Laravel\Telescope\Http\Controllers\ScheduleController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/schedule` | - | telescope |
| GET | `telescope/telescope-api/schedule/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/schedule`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/schedule/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## RedisController

**Controller**: `Laravel\Telescope\Http\Controllers\RedisController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/redis` | - | telescope |
| GET | `telescope/telescope-api/redis/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/redis`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/redis/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## ClientRequestController

**Controller**: `Laravel\Telescope\Http\Controllers\ClientRequestController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/client-requests` | - | telescope |
| GET | `telescope/telescope-api/client-requests/{telescopeEntryId}` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/client-requests`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`

### show

**Route**: ``

**URI**: `telescope/telescope-api/client-requests/{telescopeEntryId}`

**Methods**: GET

**Parameters**:
- `telescopeEntryId`

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\EntriesRepository`
- `id`: `mixed`


## MonitoredTagController

**Controller**: `Laravel\Telescope\Http\Controllers\MonitoredTagController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `telescope/telescope-api/monitored-tags` | - | telescope |
| POST | `telescope/telescope-api/monitored-tags` | - | telescope |
| POST | `telescope/telescope-api/monitored-tags/delete` | - | telescope |

### index

**Route**: ``

**URI**: `telescope/telescope-api/monitored-tags`

**Methods**: GET

**Middleware**: telescope

### store

**Route**: ``

**URI**: `telescope/telescope-api/monitored-tags`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### destroy

**Route**: ``

**URI**: `telescope/telescope-api/monitored-tags/delete`

**Methods**: POST

**Middleware**: telescope

**Method Parameters**:
- `request`: `Illuminate\Http\Request`


## RecordingController

**Controller**: `Laravel\Telescope\Http\Controllers\RecordingController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| POST | `telescope/telescope-api/toggle-recording` | - | telescope |

### toggle

**Route**: ``

**URI**: `telescope/telescope-api/toggle-recording`

**Methods**: POST

**Middleware**: telescope


## EntriesController

**Controller**: `Laravel\Telescope\Http\Controllers\EntriesController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| DELETE | `telescope/telescope-api/entries` | - | telescope |

### destroy

**Route**: ``

**URI**: `telescope/telescope-api/entries`

**Methods**: DELETE

**Middleware**: telescope

**Method Parameters**:
- `storage`: `Laravel\Telescope\Contracts\ClearableRepository`


## HomeController

**Controller**: `Laravel\Telescope\Http\Controllers\HomeController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `telescope/{view?}` | telescope | telescope |

### index

**Route**: `telescope`

**URI**: `telescope/{view?}`

**Methods**: GET

**Middleware**: telescope


## ConversationController

**Controller**: `App\Http\Controllers\Api\ConversationController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `api/conversations` | conversations.index | api, auth:sanctum |
| GET | `api/conversations/{id}` | conversations.show | api, auth:sanctum |
| PATCH | `api/conversations/{id}` | conversations.update | api, auth:sanctum |
| DELETE | `api/conversations/{id}` | conversations.destroy | api, auth:sanctum |

### index

**Route**: `conversations.index`

**URI**: `api/conversations`

**Methods**: GET

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `Illuminate\Http\Request`

### show

**Route**: `conversations.show`

**URI**: `api/conversations/{id}`

**Methods**: GET

**Parameters**:
- `id`

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `id`: `string`

### update

**Route**: `conversations.update`

**URI**: `api/conversations/{id}`

**Methods**: PATCH

**Parameters**:
- `id`

**Middleware**: api, auth:sanctum

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `id`: `string`

### destroy

**Route**: `conversations.destroy`

**URI**: `api/conversations/{id}`

**Methods**: DELETE

**Parameters**:
- `id`

**Middleware**: api, auth:sanctum

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


## BlogController

**Controller**: `App\Http\Controllers\Blog\BlogController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `blog` | blog.index | web, feature:blog |
| GET | `blog/{post}` | blog.show | web, feature:blog |

### index

**Route**: `blog.index`

**URI**: `blog`

**Methods**: GET

**Middleware**: web, feature:blog

### show

**Route**: `blog.show`

**URI**: `blog/{post}`

**Methods**: GET

**Parameters**:
- `post`

**Middleware**: web, feature:blog

**Method Parameters**:
- `post`: `App\Models\Post`


## ChangelogController

**Controller**: `App\Http\Controllers\Changelog\ChangelogController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `changelog` | changelog.index | web, feature:changelog |

### index

**Route**: `changelog.index`

**URI**: `changelog`

**Methods**: GET

**Middleware**: web, feature:changelog


## HelpCenterController

**Controller**: `App\Http\Controllers\HelpCenter\HelpCenterController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `help` | help.index | web, feature:help |
| GET | `help/{helpArticle}` | help.show | web, feature:help |

### index

**Route**: `help.index`

**URI**: `help`

**Methods**: GET

**Middleware**: web, feature:help

### show

**Route**: `help.show`

**URI**: `help/{helpArticle}`

**Methods**: GET

**Parameters**:
- `helpArticle`

**Middleware**: web, feature:help

**Method Parameters**:
- `helpArticle`: `App\Models\HelpArticle`


## PricingController

**Controller**: `App\Http\Controllers\Billing\PricingController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `pricing` | pricing | web |

### index

**Route**: `pricing`

**URI**: `pricing`

**Methods**: GET

**Middleware**: web


## ContactSubmissionController

**Controller**: `App\Http\Controllers\ContactSubmissionController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `contact` | contact.create | web, feature:contact |
| POST | `contact` | contact.store | web, feature:contact, Spatie\Honeypot\ProtectAgainstSpam |

### create

**Route**: `contact.create`

**URI**: `contact`

**Methods**: GET

**Middleware**: web, feature:contact

### store

**Route**: `contact.store`

**URI**: `contact`

**Methods**: POST

**Middleware**: web, feature:contact, Spatie\Honeypot\ProtectAgainstSpam

**Method Parameters**:
- `request`: `App\Http\Requests\StoreContactSubmissionRequest`
- `action`: `App\Actions\StoreContactSubmission`


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


## UsersTableController

**Controller**: `App\Http\Controllers\UsersTableController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `users` | users.table | web, auth, verified |
| POST | `users/bulk-soft-delete` | users.bulk-soft-delete | web, auth, verified |
| POST | `users/{user}/duplicate` | users.duplicate | web, auth, verified |
| GET | `users/{user}` | users.show | web, auth, verified |

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

**Controller**: `App\Http\Controllers\Billing\BillingDashboardController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `billing` | billing.index | web, auth, verified |

### index

**Route**: `billing.index`

**URI**: `billing`

**Methods**: GET

**Middleware**: web, auth, verified, tenant


## CreditController

**Controller**: `App\Http\Controllers\Billing\CreditController`

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

**Controller**: `App\Http\Controllers\Billing\InvoiceController`

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


## PageController

**Controller**: `App\Http\Controllers\PageController`

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
- `page`: `App\Models\Page`

### update

**Route**: `pages.update`

**URI**: `pages/{page}`

**Methods**: PUT

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant, throttle:30,1

**Method Parameters**:
- `request`: `App\Http\Requests\UpdatePageRequest`
- `page`: `App\Models\Page`

### preview

**Route**: `pages.preview`

**URI**: `pages/{page}/preview`

**Methods**: GET

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `page`: `App\Models\Page`

### duplicate

**Route**: `pages.duplicate`

**URI**: `pages/{page}/duplicate`

**Methods**: POST

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `page`: `App\Models\Page`

### destroy

**Route**: `pages.destroy`

**URI**: `pages/{page}`

**Methods**: DELETE

**Parameters**:
- `page`

**Middleware**: web, auth, verified, tenant

**Method Parameters**:
- `page`: `App\Models\Page`


## PageViewController

**Controller**: `App\Http\Controllers\PageViewController`

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


## AchievementsController

**Controller**: `App\Http\Controllers\Settings\AchievementsController`

| Method | URI | Route Name | Middleware |
|--------|-----|------------|------------|
| GET | `settings/achievements` | achievements.show | web, auth, feature:gamification |

### show

**Route**: `achievements.show`

**URI**: `settings/achievements`

**Methods**: GET

**Middleware**: web, auth, feature:gamification

**Method Parameters**:
- `request`: `Illuminate\Http\Request`
- `user`: `App\Models\User`


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



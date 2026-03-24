# Filament Admin Panel Exhaustive Audit

> Generated 2026-03-24. This documents EVERYTHING the Filament `/admin` and `/system` panels provide.

## Architecture: TWO Separate Panels

This project has **two** Filament panels, not one:

| Panel | Path | Provider | Access |
|-------|------|----------|--------|
| **Admin** | `/admin` | `AdminPanelProvider` | All authenticated users with `access admin panel` permission |
| **System** | `/system` | `SystemPanelProvider` | Super-admins only (`EnsureSuperAdmin` middleware) |

---

## PANEL 1: Admin Panel (`/admin`) — 27 routes

### Panel Configuration (`AdminPanelProvider.php`)

- **Plugins**: `FilamentStateFusionPlugin`
- **Navigation Groups**: User Management, Content, Engagement, Organizations, Billing
- **Navigation Items**: "System Settings" link to `/system` (visible to super-admins only)
- **Render Hooks**:
  - `SIDEBAR_LOGO_AFTER` → `filament.components.organization-switcher` (org switcher dropdown)
  - `SIDEBAR_NAV_START` → `filament.components.back-to-app` (back to Inertia app link)
  - `STYLES_AFTER` → `filament.components.admin-panel-sidebar-styles` (custom sidebar CSS)
- **Auth Middleware**: `Authenticate`, `EnsureSetupComplete`
- **Custom Middleware**: `FlashOrganizationSwitchNotification`
- **Resource Discovery**: `app/Filament/Resources`
- **Widget Discovery**: `app/Filament/Widgets`
- **Max Content Width**: `Width::Full`

### Admin Panel Widgets

| Widget | Type | Purpose |
|--------|------|---------|
| `StatsOverviewWidget` | Stats | Total Users, New This Month, Verified Users |
| `InstallNextStepsWidget` | Custom | Post-install next steps (session-based, dismissible) |
| `AccountWidget` | Built-in | Account info |

### Admin Panel Resources (7 resources)

#### 1. UserResource
- **Model**: `App\Models\User`
- **Nav Group**: User Management (sort 10)
- **CRUD**: List, Create, View, Edit
- **Global Search**: name, email
- **Relation Managers**: `CategoriesRelationManager` (user-category assignments)
- **Tenant Scoping**: Custom — super-admins see all; org admins see only users in their organizations
- **Table**: via `UsersTable` (uses `HasStandardExports` — XLSX/CSV export)
- **Form**: via `UserForm`
- **Infolist**: via `UserInfolist`
- **Has Inertia Equivalent?**: NO dedicated admin user management page. Inertia has `settings/` pages but those are user self-service, not admin CRUD.

#### 2. RoleResource
- **Model**: `Spatie\Permission\Models\Role`
- **Nav Group**: User Management (sort 20)
- **CRUD**: List, Create, View, Edit
- **Global Search**: name, guard_name
- **Table**: via `RolesTable`
- **Form**: via `RoleForm`
- **Infolist**: via `RoleInfolist`
- **Has Inertia Equivalent?**: PARTIAL — `settings/roles.tsx` exists but likely org-level role management, not full admin CRUD.

#### 3. CategoryResource
- **Model**: `App\Models\Category`
- **Nav Group**: (none specified, likely default)
- **CRUD**: Manage (simple list with inline create/edit)
- **Page**: `ManageCategories` (single simple page)
- **Has Inertia Equivalent?**: NO

#### 4. OrganizationInvitationResource
- **Model**: `App\Models\OrganizationInvitation`
- **Nav Group**: Organizations (sort 20)
- **CRUD**: List, Create, Edit
- **Uses**: `ScopesToCurrentTenant` trait
- **Global Search**: email
- **Navigation Badge**: Count of pending invitations (warning color)
- **Table**: via `OrganizationInvitationsTable`
- **Form**: via `OrganizationInvitationForm`
- **Has Inertia Equivalent?**: PARTIAL — `invitations/` dir exists in Inertia pages.

#### 5. EnterpriseInquiryResource
- **Model**: `App\Models\EnterpriseInquiry`
- **Nav Group**: Engagement (sort 20)
- **CRUD**: List, View, Edit (NO create — `canCreate()` returns false)
- **Global Search**: name, email, company
- **Navigation Badge**: Count of "new" status inquiries (warning color)
- **Table**: via `EnterpriseInquiriesTable` (uses `HasStandardExports`)
- **Form**: via `EnterpriseInquiryForm`
- **Infolist**: via `EnterpriseInquiryInfolist`
- **Has Inertia Equivalent?**: PARTIAL — `enterprise-inquiries/` dir exists.

#### 6. MailTemplateResource
- **Model**: `App\Models\MailTemplate` (database-mail)
- **Nav Group**: (not specified)
- **CRUD**: List, Edit (NO create — `canCreate()` returns false)
- **Global Search**: name, subject
- **Access**: System panel only + super-admin only (despite being in admin resources dir)
- **Table**: via `MailTemplatesTable`
- **Form**: via `MailTemplateForm`
- **Has Inertia Equivalent?**: NO

#### 7. CreditPackResource
- **Model**: `App\Models\CreditPack`
- **Nav Group**: Billing
- **CRUD**: List, Create, Edit
- **Table**: via `CreditPacksTable` — reorderable (`sort_order`), uses `HasStandardExports`, `TrashedFilter`, force delete/restore bulk actions
- **Form**: via `CreditPackForm`
- **Has Inertia Equivalent?**: NO

#### 8. VoucherResource
- **Model**: `BeyondCode\Vouchers\Models\Voucher`
- **Nav Group**: Billing (sort 30)
- **CRUD**: List, Create, Edit
- **Global Search**: code
- **Table**: via `VouchersTable`
- **Form**: via `VoucherForm`
- **Has Inertia Equivalent?**: NO

#### 9. AffiliateResource
- **Model**: `Modules\Billing\Models\Affiliate`
- **Nav Group**: Billing (sort 40)
- **CRUD**: Manage (simple list page)
- **Access**: System panel only
- **Global Search**: affiliate_code, user.name
- **Table**: via `AffiliatesTable`
- **Has Inertia Equivalent?**: NO

### Shared Concerns/Traits

| Trait | Purpose |
|-------|---------|
| `ScopesToCurrentTenant` | Scopes resource queries to current org via `organization_id`; super-admins bypass |
| `HasStandardExports` | Provides XLSX + CSV header export actions and bulk export actions via `pxlrbt/filament-excel` |

---

## PANEL 2: System Panel (`/system`) — 71 routes

### Panel Configuration (`SystemPanelProvider.php`)

- **Access**: Super-admin only (`EnsureSuperAdmin` auth middleware)
- **Plugins**:
  - `FilamentStateFusionPlugin`
  - `FeatureFlagPlugin` (Pennant feature flag management UI)
  - `ActivityLogPlugin` (activity logs in Settings > System nav group)
- **Navigation Groups**: User Management, Settings > App, Organizations, Billing, Settings > Integrations (collapsed), Settings > System (collapsed), Settings > Features (collapsed), Content & Legal (collapsed)
- **Conditional Module Resource Discovery**:
  - `modules.announcements` → `Modules\Announcements\Filament\Resources`
  - `modules.blog` → `Modules\Blog\Filament\Resources`
  - `modules.changelog` → `Modules\Changelog\Filament\Resources`
  - `modules.contact` → `Modules\Contact\Filament\Resources`
  - `modules.help` → `Modules\Help\Filament\Resources`
- **Conditional Widget Discovery**:
  - `modules.gamification` → `Modules\Gamification\Filament\Widgets`
- **Widget Discovery**: `app/Filament/System/Widgets`
- **Page Discovery**: `app/Filament/System/Pages`
- **Render Hooks**:
  - `SIDEBAR_LOGO_AFTER` → `filament.components.organization-switcher`
  - `SIDEBAR_NAV_START` → `filament.components.back-to-app`
  - `STYLES_AFTER` → `filament.components.system-panel-sidebar-styles`
- **Max Content Width**: `Width::Full`

### System Panel Resources (7 resources)

#### 1. OrganizationResource
- **Model**: `App\Models\Organization`
- **Nav Group**: Organizations (sort 10)
- **CRUD**: List, Create, Edit
- **Global Search**: name, slug
- **Relation Managers**:
  - `DomainsRelationManager` — manage custom domains per org
  - `InvitationsRelationManager` — manage invitations per org
  - `UsersRelationManager` — manage members per org (attach/detach/edit/delete)
- **Soft Deletes**: Yes (TrashedFilter)
- **Table**: via `OrganizationsTable` (uses `HasStandardExports`)
- **Form**: via `OrganizationForm`
- **Has Inertia Equivalent?**: PARTIAL — `organizations/` dir exists but likely for org member self-service.

#### 2. PermissionResource
- **Model**: `Spatie\Permission\Models\Permission`
- **Nav Group**: User Management (sort 30)
- **CRUD**: List, View (NO create — `canCreate()` returns false)
- **Custom Actions**:
  - **"Sync from routes"** header action — runs `permission:sync-routes` artisan command
- **Table**: via `PermissionsTable`
- **Has Inertia Equivalent?**: NO

#### 3. TermsVersionResource
- **Model**: `App\Models\TermsVersion`
- **Nav Group**: Content & Legal (sort 20)
- **CRUD**: List, Create, Edit
- **Access**: Super-admin only
- **Global Search**: title
- **Table**: via `TermsVersionsTable` (uses `HasStandardExports`)
- **Form**: via `TermsVersionForm` (title, type (TermsType enum), content, effective_at, is_required, share_to_all_orgs)
- **Has Inertia Equivalent?**: NO

#### 4. AuditLogResource
- **Model**: `App\Models\AuditLog`
- **Nav Group**: Settings > App (sort 40)
- **CRUD**: List only (NO create)
- **Access**: Super-admin only
- **Columns**: Date, Actor, Organization, Action (badge), Subject Type, Subject ID, IP (hidden by default)
- **Filters**: Action type filter (theme.saved, theme.reset, logo.uploaded, branding changes, feature toggled, member invited/removed, role created/deleted, system setting changed, slug changed, domain added/removed)
- **Has Inertia Equivalent?**: PARTIAL — `settings/audit-log.tsx` exists but likely org-scoped.

#### 5. VisibilityDemoResource
- **Model**: `App\Models\VisibilityDemo`
- **Nav Group**: (not specified)
- **CRUD**: List, Create, Edit
- **Purpose**: Demo/test resource for the visibility sharing system
- **Has Inertia Equivalent?**: NO (dev/demo only)

### Module Resources (in System Panel)

#### 6. AnnouncementResource (modules/announcements)
- **Model**: `Modules\Announcements\Models\Announcement`
- **Nav Group**: Content (sort 20)
- **CRUD**: List, Create, Edit
- **Tenant Scoped**: Yes (filtered by `TenantContext::id()`)
- **Table**: Reorderable by position; columns: title, level (badge), scope (badge), is_active, featured_flag. Custom row actions: feature/unfeature
- **Has Inertia Equivalent?**: PARTIAL — `announcements/` dir exists (likely public-facing view).

#### 7. PostResource (modules/blog)
- **Model**: `Modules\Blog\Models\Post`
- **Nav Group**: Content (sort 10)
- **CRUD**: List, Create, View, Edit
- **Soft Deletes**: Yes
- **Feature Flag**: `BlogFeature`
- **Table**: Columns include author, title, status, is_published, views, created_at. `TrashedFilter`. Export header action. Custom row actions: feature/unfeature posts. Bulk: export, delete, force delete, restore.
- **Has Inertia Equivalent?**: PARTIAL — `posts/` and `blog/` dirs exist (likely public view).

#### 8. ChangelogEntryResource (modules/changelog)
- **Model**: `Modules\Changelog\Models\ChangelogEntry`
- **Nav Group**: Content (sort 20)
- **CRUD**: List, Create, View, Edit
- **Soft Deletes**: Yes
- **Feature Flag**: `ChangelogFeature`
- **Table**: Columns: title, version, tags, type (badge with color coding: Added=success, Changed=info, Fixed=warning, Removed=danger, Security=danger), is_published, released_at. TrashedFilter. Export. Bulk actions.
- **Has Inertia Equivalent?**: PARTIAL — `changelog/` dir exists (likely public view).

#### 9. ContactSubmissionResource (modules/contact)
- **Model**: `Modules\Contact\Models\ContactSubmission`
- **Nav Group**: Engagement (sort 10)
- **CRUD**: List, View, Edit (NO create)
- **Feature Flag**: `ContactFeature`
- **Navigation Badge**: Count of "new" status
- **Table**: name, email, subject, status (badge), created_at. Export.
- **Has Inertia Equivalent?**: NO admin equivalent. `contact/` likely the public form.

#### 10. HelpArticleResource (modules/help)
- **Model**: `Modules\Help\Models\HelpArticle`
- **Nav Group**: Content (sort 30)
- **CRUD**: List, Create, View, Edit
- **Soft Deletes**: Yes
- **Feature Flag**: `HelpFeature`
- **Table**: Columns include is_published. TrashedFilter. Export. Custom row actions: feature/unfeature. Bulk: export, delete, force/restore.
- **Has Inertia Equivalent?**: PARTIAL — `help/` dir exists (likely public view).

### System Panel Custom Pages (30 pages)

#### Settings Pages (DB-backed via spatie/laravel-settings)

| # | Page | Settings Class | Nav Group | Fields |
|---|------|---------------|-----------|--------|
| 1 | `ManageApp` | `AppSettings` | Settings > App | App name, URL, timezone, locale, debug, maintenance mode |
| 2 | `ManageAuth` | `AuthSettings` | Settings > App | Registration enabled, email verification required, 2FA enforcement |
| 3 | `ManageSEO` | `SeoSettings` | Settings > App | Meta title, meta description, OG image URL |
| 4 | `ManageCookieConsent` | `CookieConsentSettings` | Settings > App | Enabled toggle |
| 5 | `ManageBilling` | `BillingSettings` | Settings > App | Billing enabled, provider (stripe/paddle/lemon-squeezy), currency, trial days, seat-based billing, credit system enabled, free credits |
| 6 | `ManageLogging` | `LoggingSettings` | Settings > App | Log channel, log level |
| 7 | `ManageMail` | `MailSettings` | Settings > Integrations | Mailer, host, port, username, password, encryption, from address/name |
| 8 | `ManageStripe` | `StripeSettings` | Settings > Integrations | API key, signing secret, currency |
| 9 | `ManagePaddle` | `PaddleSettings` | Settings > Integrations | Vendor ID, API key, public key, sandbox mode |
| 10 | `ManageLemonSqueezy` | `LemonSqueezySettings` | Settings > Integrations | API key, signing secret, store, path, currency locale, generic variant ID |
| 11 | `ManageIntegrations` | `IntegrationSettings` | Settings > Integrations | Slack webhook URL, Google Analytics ID, GA4 measurement ID, GA4 API secret |
| 12 | `ManagePrism` | `PrismSettings` | Settings > Integrations | Default model, default provider, API key |
| 13 | `ManageAI` | `AiSettings` | Settings > Integrations | Default provider, default for images/embeddings/audio, API keys (OpenAI, Anthropic, Google, xAI, ElevenLabs, Jina), Thesys key + model |
| 14 | `ManageBroadcasting` | `BroadcastingSettings` | Settings > Integrations | Default broadcaster, Pusher key/secret/app ID/cluster, Ably key |
| 15 | `ManageBackup` | `BackupSettings` | Settings > System | Name, retention days (all/daily/weekly/monthly), max size MB |
| 16 | `ManageMedia` | `MediaSettings` | Settings > System | Disk name, max file size |
| 17 | `ManageFilesystem` | `FilesystemSettings` | Settings > System | Default disk, S3 key/secret/region/bucket/URL |
| 18 | `ManageMemory` | `MemorySettings` | Settings > System | Max recall results, middleware recall limit, memory enabled |
| 19 | `ManageScout` | `ScoutSettings` | Settings > System | Driver, queue, Typesense host/port/protocol/API key/collection prefix |
| 20 | `ManagePermissions` | `PermissionsSettings` | Settings > System | Teams enabled toggle |
| 21 | `ManageTenancy` | `TenancySettings` | Settings > System | Multi-org enabled, default org name, auto-create org, auto-assign role |
| 22 | `ManagePerformance` | `PerformanceSettings` | Settings > System | Cache driver, session driver, queue connection, Redis host/port/password |
| 23 | `ManageMonitoring` | `MonitoringSettings` | Settings > System | Sentry DSN, Sentry environment, sample rate, traces sample rate |
| 24 | `ManageSecurity` | `SecuritySettings` | Settings > System | Force HTTPS, HSTS enabled, HSTS max age, CSP enabled, CSP policy, rate limit per minute |
| 25 | `ManageActivityLog` | `ActivityLogSettings` | Settings > Features | Activity log enabled, retention days |
| 26 | `ManageImpersonate` | `ImpersonateSettings` | Settings > Features | Impersonate enabled, require permission select |
| 27 | `ManageFeatureFlags` | `FeatureFlagSettings` | Settings > Features | Globally disabled modules |
| 28 | `ManageInfrastructure` | `InfrastructureSettings` | Settings > System | Writes to `.env` directly (APP_KEY, DB_*, CACHE_*, SESSION_*, QUEUE_*, REDIS_*, LOG_*). Custom `afterSave()` warns about queue restart |

#### Custom (Non-Settings) Pages

| # | Page | Nav Group | Purpose |
|---|------|-----------|---------|
| 29 | `RevenueDashboard` | Billing | Revenue analytics dashboard with `RevenueOverviewStats` widget (MRR, active subscriptions, monthly revenue, churn rate) + `Ga4OverviewWidget` (visitors, page views, top page) |
| 30 | `ProductAnalytics` | Settings > System | Pan product analytics dashboard — impressions, hovers, clicks, top elements. Custom Blade view with table |
| 31 | `ApiDocs` | Settings > System | Link to Scramble API documentation (feature-flagged) |
| 32 | `ManageOrganizationOverrides` | Settings > System | Per-organization settings override management. Custom page with table (InteractsWithTable), not a SettingsPage. Allows setting per-org overrides for 7 overridable settings groups |
| 33 | `SetupWizard` | — | Post-install setup wizard |

**None of these 33 system pages have Inertia equivalents.**

### System Panel Widgets (5 widgets)

| Widget | Type | Location |
|--------|------|----------|
| `RevenueOverviewStats` | StatsOverview | System/Widgets/Billing/ — MRR (with sparkline + trend), Active Subscriptions (with growth %), Monthly Revenue, Churn Rate |
| `Ga4OverviewWidget` | StatsOverview | System/Widgets/ — GA4 visitors (7d), page views (7d), top page |
| `ProductAnalyticsOverviewWidget` | StatsOverview | System/Widgets/ — Pan impressions, clicks, hovers, top by clicks |
| `UserLevelWidget` | StatsOverview | modules/gamification — User level, XP, achievements count (feature-flagged) |
| `AccountWidget` | Built-in | Standard Filament account widget |
| `FilamentInfoWidget` | Built-in | Filament version info |

### System Panel Blade Views

| View | Purpose |
|------|---------|
| `filament/components/organization-switcher.blade.php` | Org switcher in sidebar |
| `filament/components/back-to-app.blade.php` | "Back to app" link |
| `filament/components/admin-panel-sidebar-styles.blade.php` | Admin panel sidebar CSS |
| `filament/components/system-panel-sidebar-styles.blade.php` | System panel sidebar CSS |
| `filament/pages/billing/analytics/revenue-dashboard.blade.php` | Revenue dashboard layout |
| `filament/pages/manage-organization-overrides.blade.php` | Org overrides layout |
| `filament/pages/product-analytics.blade.php` | Product analytics layout |
| `filament/pages/setup-wizard.blade.php` | Setup wizard layout |
| `filament/widgets/install-next-steps.blade.php` | Post-install widget template |

---

## Relation Managers (4 total)

| Relation Manager | Parent Resource | Relationship | Actions |
|-----------------|-----------------|-------------|---------|
| `CategoriesRelationManager` | UserResource (admin) | user.categories | Attach/Detach/Edit |
| `DomainsRelationManager` | OrganizationResource (system) | org.domains | Attach/Detach/Edit/Delete |
| `InvitationsRelationManager` | OrganizationResource (system) | org.invitations | Attach/Detach/Edit/Delete |
| `UsersRelationManager` | OrganizationResource (system) | org.users | Attach/Detach/Edit/Delete + bulk detach/delete |

---

## Third-Party Filament Plugins

| Plugin | Panel | Purpose |
|--------|-------|---------|
| `FilamentStateFusionPlugin` | Both | Real-time state management |
| `FeatureFlagPlugin` | System | Pennant feature flag management UI |
| `ActivityLogPlugin` | System | Activity log viewer (nav group: Settings > System) |
| `pxlrbt/filament-excel` | Both (via HasStandardExports) | XLSX/CSV export for all resources |

---

## Complete Feature-by-Feature Migration Assessment

| # | Feature | Panel | Location | Has Inertia Equivalent? | Migration Complexity | Notes |
|---|---------|-------|----------|------------------------|---------------------|-------|
| 1 | **User CRUD** | Admin | `Resources/Users` | NO | HIGH | Need list/create/view/edit pages, categories relation, tenant scoping, global search |
| 2 | **Role CRUD** | Admin | `Resources/Roles` | PARTIAL (`settings/roles.tsx`) | MEDIUM | Need to verify if existing covers full admin CRUD |
| 3 | **Category Management** | Admin | `Resources/Categories` | NO | LOW | Simple manage page |
| 4 | **Org Invitation Management** | Admin | `Resources/OrganizationInvitations` | PARTIAL (`invitations/`) | MEDIUM | Pending badge, tenant scoping |
| 5 | **Enterprise Inquiry Management** | Admin | `Resources/EnterpriseInquiries` | PARTIAL (`enterprise-inquiries/`) | MEDIUM | View/edit only, badge, export |
| 6 | **Mail Template Editor** | Admin | `Resources/MailTemplates` | NO | HIGH | Rich template editing, no create |
| 7 | **Credit Pack Management** | Admin | `Resources/CreditPacks` | NO | MEDIUM | Reorderable, soft deletes, export |
| 8 | **Voucher Management** | Admin | `Resources/Vouchers` | NO | MEDIUM | Full CRUD |
| 9 | **Affiliate Management** | System | `Resources/Billing/Affiliates` | NO | LOW | Simple manage page |
| 10 | **Organization CRUD** | System | `System/Resources/Organizations` | PARTIAL (`organizations/`) | HIGH | 3 relation managers (domains, invitations, users), soft deletes, export |
| 11 | **Permission Viewer** | System | `System/Resources/Permissions` | NO | MEDIUM | List/view + sync-from-routes action |
| 12 | **Terms & Privacy Versions** | System | `System/Resources/TermsVersions` | NO | MEDIUM | Full CRUD, type enum, effective date |
| 13 | **Audit Log Viewer** | System | `System/Resources/AuditLogs` | PARTIAL (`settings/audit-log.tsx`) | MEDIUM | Filters, multiple action types |
| 14 | **Visibility Demo** | System | `System/Resources/VisibilityDemos` | NO | LOW | Dev/demo only |
| 15 | **Announcement CRUD** | System | Module: announcements | PARTIAL (public view only) | MEDIUM | Reorderable, feature/unfeature, tenant scoped |
| 16 | **Blog Post CRUD** | System | Module: blog | PARTIAL (public view only) | HIGH | Rich editor, soft deletes, feature/unfeature, author, views count, feature-flagged |
| 17 | **Changelog Entry CRUD** | System | Module: changelog | PARTIAL (public view only) | MEDIUM | Tags, type enum with colors, soft deletes, feature-flagged |
| 18 | **Contact Submission Viewer** | System | Module: contact | NO admin equiv | LOW-MEDIUM | Read/edit only, badge |
| 19 | **Help Article CRUD** | System | Module: help | PARTIAL (public view only) | MEDIUM | Soft deletes, feature/unfeature, feature-flagged |
| 20-47 | **28 Settings Pages** | System | `System/Pages/Manage*` | NO | VERY HIGH | Each manages DB-backed settings. Infrastructure page writes .env directly. 26 settings classes total |
| 48 | **Revenue Dashboard** | System | `System/Pages/Billing/Analytics` | NO | HIGH | MRR calc, sparklines, subscription growth, churn rate, GA4 integration |
| 49 | **Product Analytics** | System | `System/Pages/ProductAnalytics` | NO | MEDIUM | Pan analytics data display |
| 50 | **API Docs Page** | System | `System/Pages/ApiDocs` | NO | LOW | Just a link to Scramble docs |
| 51 | **Org Settings Overrides** | System | `System/Pages/ManageOrganizationOverrides` | NO | HIGH | Complex custom page with table, per-org override management for 7 settings groups |
| 52 | **Setup Wizard** | System | `System/Pages/SetupWizard` | NO | MEDIUM | Post-install setup flow |
| 53 | **Feature Flag UI** | System | Plugin: `FeatureFlagPlugin` | PARTIAL (`settings/features.tsx`) | MEDIUM | Plugin provides full management |
| 54 | **Activity Log Viewer** | System | Plugin: `ActivityLogPlugin` | NO | MEDIUM | Plugin provides log browsing |
| 55 | **Dashboard Stats** | Admin | `Widgets/StatsOverviewWidget` | NO | LOW | User stats |
| 56 | **Revenue Stats Widget** | System | `System/Widgets/Billing/RevenueOverviewStats` | NO | HIGH | Complex MRR/churn calculations |
| 57 | **GA4 Widget** | System | `System/Widgets/Ga4OverviewWidget` | NO | MEDIUM | Google Analytics data |
| 58 | **Product Analytics Widget** | System | `System/Widgets/ProductAnalyticsOverviewWidget` | NO | LOW-MEDIUM | Pan stats |
| 59 | **Gamification Widget** | System | Module: gamification | NO | LOW | Level/XP/achievements |
| 60 | **XLSX/CSV Export** | Both | `HasStandardExports` trait | NO | MEDIUM | Every resource with exports needs replacement |
| 61 | **Org Switcher** | Both | Render hook blade component | NO | MEDIUM | Sidebar org switcher |
| 62 | **Global Search** | Both | Built-in Filament | NO | HIGH | Cross-resource search across all models |

---

## CRITICAL: Features with NO Inertia Equivalent At All

These will be **completely lost** if Filament is removed:

### Data Management (CRUD)
1. User admin CRUD (list all users, create, view, edit across orgs)
2. Category management
3. Mail template editor
4. Credit pack management (with reordering)
5. Voucher management
6. Affiliate management
7. Organization CRUD (with domains, invitations, members relation managers)
8. Permission viewer + sync-from-routes
9. Terms & Privacy version management
10. Visibility demo management

### ALL 28 System Settings Pages
11. App settings (name, URL, timezone, locale, debug, maintenance)
12. Auth settings (registration, email verification, 2FA enforcement)
13. SEO settings (meta title, description, OG image)
14. Cookie consent toggle
15. Billing settings (provider, currency, trials, seats, credits)
16. Logging settings (channel, level)
17. Mail settings (mailer, SMTP config)
18. Stripe settings (API keys, currency)
19. Paddle settings (vendor, keys, sandbox)
20. Lemon Squeezy settings (API key, store, currency)
21. Integration settings (Slack webhook, GA IDs)
22. Prism/LLM settings (model, provider, API key)
23. AI settings (providers, API keys for OpenAI/Anthropic/Google/xAI/ElevenLabs/Jina/Thesys)
24. Broadcasting settings (Pusher/Ably config)
25. Backup settings (retention, size limits)
26. Media settings (disk, max file size)
27. Filesystem settings (S3 config)
28. Memory settings (recall limits)
29. Scout/Typesense settings
30. Permissions settings (teams toggle)
31. Tenancy settings (multi-org, auto-create)
32. Performance settings (cache/session/queue drivers, Redis)
33. Monitoring settings (Sentry DSN, sample rates)
34. Security settings (HTTPS, HSTS, CSP, rate limiting)
35. Activity log settings (enabled, retention)
36. Impersonate settings (enabled, permission)
37. Feature flag settings (disabled modules)
38. Infrastructure settings (.env writer)

### Dashboards & Analytics
39. Revenue Analytics Dashboard (MRR, subscriptions, churn, GA4)
40. Product Analytics Dashboard (Pan impressions/hovers/clicks)
41. Admin dashboard stats (user counts)

### Special Pages
42. Organization settings overrides management
43. Setup wizard
44. API docs link page

### Plugins & Built-in Features
45. Feature flag management UI (FeatureFlagPlugin)
46. Activity log viewer (ActivityLogPlugin)
47. Global search across all resources
48. XLSX/CSV export for all resources
49. Organization switcher in sidebar

---

## Summary Statistics

| Metric | Count |
|--------|-------|
| Total Filament routes | **98** (27 admin + 71 system) |
| Resources (admin panel) | **9** |
| Resources (system panel) | **5** + 5 module resources |
| Settings pages | **28** |
| Custom pages | **5** (RevenueDashboard, ProductAnalytics, ApiDocs, OrgOverrides, SetupWizard) |
| Widgets | **6** custom + 2 built-in |
| Relation managers | **4** |
| Blade views | **9** |
| Filament plugins | **4** |
| Features with NO Inertia equivalent | **~49** |
| Features with PARTIAL Inertia equivalent | **~10** |

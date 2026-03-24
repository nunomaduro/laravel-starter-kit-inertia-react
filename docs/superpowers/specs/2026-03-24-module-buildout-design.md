# Module Build-Out & Admin Migration — Design Spec

**Date:** 2026-03-24
**Status:** Draft
**Goal:** Build out all 13 modules to full production quality, standardize on one module architecture, add complete Inertia CRUD UIs, and remove the Filament `/admin` panel.

---

## Architecture Decisions

1. **Single module pattern:** All modules use `ModuleProvider` (new pattern) with manifests, AI context, and cross-module relationships. Legacy `ModuleServiceProvider` retired.
2. **No Filament `/admin`:** Org-scoped CRUD lives in Inertia. Global/system resources move to `/system`.
3. **Full CRUD in Inertia:** Every module gets list (DataTable), create, edit, detail views.
4. **Modular testing:** Module tests live inside `modules/{name}/tests/` and can run independently of core tests.
5. **Enable/disable with zero code changes:** `config/modules.php` toggles everything — routes, nav, service providers, Filament resources.

---

## Phase A: Module Foundation & Standardization

### A0. Extend ModuleProvider Base Class (PREREQUISITE)

The current `ModuleProvider` (`app/Modules/Support/ModuleProvider.php`) only handles migrations, model registry, and AI context. It does NOT handle:
- Route loading
- Pennant feature flag registration via `ModuleFeatureRegistry`
- DataTable controller registration
- Enabled/disabled toggle check

**Before migrating any module, extend `ModuleProvider` to support:**
1. `loadRoutes()` — load `routes/web.php` from the module directory (gated by `config('modules.{key}')`)
2. `registerFeature()` — register with `ModuleFeatureRegistry::registerInertiaFeature()` and `registerRouteFeature()` so Inertia pages can check `usePage().props.features.{module}`
3. `registerDataTable()` — register DataTable controllers if the module has a `DataTables/` directory
4. `isEnabled(): bool` — check `config('modules.{key}')` and short-circuit boot if disabled
5. `registerFilamentResources()` — for `/system` panel discovery

This unifies all capabilities of the legacy `ModuleServiceProvider` into `ModuleProvider`.

### A1. Rename and Register CRM/HR Modules

**Rename directories** for consistency with other modules:
- `modules/module-crm/` → `modules/crm/`
- `modules/module-hr/` → `modules/hr/`

Update namespaces, composer.json, PSR-4 autoload, and any references.

**Add to `config/modules.php`:**
```php
'crm' => true,
'hr' => true,
```

**Create `module.json`** manifests for CRM and HR so `ModuleLoader` discovers them.

CRM/HR are currently loaded via `composer.json` `extra.laravel.providers` — they'll switch to `ModuleLoader` discovery like all other modules.

### A2. Migrate ALL Legacy Modules to ModuleProvider

**Affected (11 modules):** blog, changelog, help, contact, announcements, billing, page-builder, dashboards, reports, gamification, workflows

All currently extend the legacy `ModuleServiceProvider`. CRM and HR already use `ModuleProvider` but need updates after A0 extends the base class.

For each legacy module:
1. Create new service provider extending `ModuleProvider`
2. Implement `manifest()` returning `ModuleManifest` with name, version, models, pages, navigation
3. Implement `ProvidesAIContext` (systemPrompt, tools, searchableModels)
4. Route loading, migration loading, policy registration handled automatically by the updated `ModuleProvider` base
5. Feature flag sharing with Inertia handled by `registerFeature()` in base class — replaces Pennant `featureClass()` for module toggling (Pennant still available for org-level overrides within enabled modules)
6. Update `module.json` to point to new provider
7. Update `composer.json` autoload if namespace changes
8. Delete old service provider

**Pattern to follow:** `CrmModuleServiceProvider` (after A0 updates) — it's the reference implementation.

### A3. Standardize Module Directory Structure

**Convention:** All module Inertia pages live in `resources/js/pages/{module}/`, NOT inside the module directory. Inertia resolves from `resources/js/pages/` so this is the only location that works without custom Vite config. Controllers and backend code live inside the module.

Every module should follow:
```
modules/{name}/
├── module.json
├── composer.json
├── src/
│   ├── Providers/{Name}ModuleServiceProvider.php
│   ├── Models/
│   ├── Policies/
│   ├── Actions/
│   ├── Http/Controllers/
│   ├── DataTables/
│   └── Filament/Resources/          (for /system panel only)
├── routes/
│   └── web.php
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
└── tests/
    ├── Feature/
    └── Unit/
```

### A4. Sidebar Navigation

Add new collapsible nav groups to the Inertia app sidebar (`app-sidebar.tsx`):

```
Platform          (existing)
  Dashboard
  Chat
Organization      (existing)
  Members
  Settings
CRM               (NEW)
  Contacts
  Deals
  Pipelines
HR                (NEW)
  Employees
  Departments
  Leave Requests
Content           (existing, expanded)
  Blog
  Pages
  Changelog
  Help Center
  Announcements
Analytics         (NEW)
  Dashboards
  Reports
Billing           (existing)
  Plans
  Invoices
  Credits
Engagement        (NEW)
  Gamification
  Workflows
```

Each nav group only renders if its module is enabled (`config('modules.{name}')`). The sidebar reads module configs to determine what to show.

### A5. Module Test Isolation

Each module gets its own `tests/` directory:
```
modules/{name}/tests/
├── Feature/
│   ├── {Name}ControllerTest.php
│   └── ...
└── Unit/
    ├── {Name}PolicyTest.php
    └── ...
```

Update `phpunit.xml` / `Pest.php` to discover module tests. Add a Composer script:
- `composer test` — core tests only (tests/)
- `composer test:modules` — module tests only (modules/*/tests/)
- `composer test:all` — everything

---

## Phase B: Build Out 7 Incomplete Modules (Inertia CRUD)

Each module gets the same treatment: DataTable list, create form, edit form, detail/show view, proper validation, and polished UX per DESIGN.md.

### B1. CRM Module

**Models:** Contact, Deal, Pipeline, Activity (already exist)
**Controllers:** ContactController, DealController already exist in `app/Http/Controllers/Crm/`
**Pages already exist:** `crm/contacts/{index, create, edit}`, `crm/deals/index`

**What's missing:**
- Contact show/detail page
- Deal create/edit pages
- Pipeline management page (CRUD for sales stages)
- Activity timeline view
- Deal kanban board (DataTable has kanban layout mode)
- Move controllers from `app/Http/Controllers/Crm/` into the module: `modules/module-crm/src/Http/Controllers/`
- Move routes from `routes/web.php` into `modules/module-crm/routes/web.php`
- Move pages from `resources/js/pages/crm/` — keep in place (Inertia resolves from `resources/js/pages/`)

### B2. HR Module

**Models:** Employee, Department, LeaveRequest (already exist)
**Controllers:** EmployeeController, LeaveRequestController already exist
**Pages already exist:** `hr/employees/{index, create, edit}`, `hr/leave-requests/index`

**What's missing:**
- Employee show/detail page
- Department CRUD pages (list, create, edit)
- Leave request create/edit pages
- Leave request approval flow (approve/reject actions)
- Employee onboarding checklist view
- Move controllers into module
- Move routes into module

### B3. Dashboards Module

**Models:** Dashboard (already exists)
**Pages already exist:** `dashboards/{index, edit, show}`

**What's missing:**
- Dashboard create page
- Widget configuration (already has Puck integration)
- Dashboard sharing/visibility controls
- Move controller into module
- Module-specific Filament resource for `/system` (super-admin can see all org dashboards)

### B4. Reports Module

**Models:** Report, ReportOutput (already exist)
**Pages already exist:** `reports/{index, edit, show}`

**What's missing:**
- Report create page with builder UI (select data source, filters, output format)
- Report output viewer (PDF preview, CSV download)
- Scheduled report configuration
- Move controller into module

### B5. Gamification Module

**Models:** Currently none — needs Level, Achievement, UserAchievement, PointTransaction
**Controllers:** Minimal (1 controller)
**Pages:** None exist

**What needs building (most work):**
- Models + migrations for levels, achievements, point transactions
- Factories + policies for new models
- Leaderboard page (org-scoped, with DataTable)
- Achievements list page (with progress indicators)
- User profile achievements section
- Point transaction history
- Admin configuration (point rules, achievement definitions) — in `/system` Filament

### B6. Page Builder Module

**Models:** Page, PageRevision (already exist)
**Controllers:** PageController, PageViewController already exist
**Pages:** Already functional via Puck editor

**What's missing:**
- Page list page with DataTable (currently basic list)
- Page revision history view
- Move controllers into module
- Move routes into module
- The Puck editor integration already works — just needs DataTable wrapper and module encapsulation

### B7. Workflows Module

**Models:** None (wrapper around laravel-workflow)
**Controllers:** None
**Pages:** None exist

**What needs building:**
- Workflow list page (read from laravel-workflow tables)
- Workflow detail/status page (timeline of activities)
- Manual trigger page (start a workflow with parameters)
- Integration with Waterline UI (already at `/waterline` for super-admin)
- Org-scoped workflow filtering

---

## Phase C: Remove `/admin` Panel

### C1. Move Global Resources to `/system`

| Resource | Current | Move To |
|----------|---------|---------|
| MailTemplateResource | `/admin` | `/system` |
| EnterpriseInquiryResource | `/admin` | `/system` |
| CreditPackResource | `/admin` | `/system` |
| AffiliateResource | `/admin` | `/system` |
| VoucherResource | `/admin` | `/system` |

Move the PHP files from `app/Filament/Resources/` to `app/Filament/System/Resources/`. Update namespaces. Register in SystemPanelProvider.

### C2. Remove Redundant Resources

These already have full Inertia equivalents:
- UserResource — Inertia `/users` has full DataTable + CRUD
- RoleResource — Inertia `/settings/roles` has CRUD
- OrganizationInvitationResource — Inertia `/organizations/members` handles invitations

Delete these Filament resources entirely.

### C3. Migrate Categories to Inertia

CategoryResource needs an Inertia equivalent:
- Categories list page (DataTable)
- Category create/edit forms
- This is small — Category has: name, slug, organization_id

### C4. Disable Then Delete AdminPanelProvider

**Rollback strategy:** First disable, then delete after verification.

Step 1 — Disable (reversible):
1. Comment out AdminPanelProvider registration (don't delete the file yet)
2. Run full test suite — verify nothing depends on `/admin` routes
3. Test all Inertia pages that replaced admin functionality
4. Tag git commit: `git tag pre-admin-removal`

Step 2 — Delete (after verification):
1. Remove `app/Providers/Filament/AdminPanelProvider.php`
2. Remove admin panel middleware, render hooks, sidebar styles
3. Remove `/admin` routes
4. Update any links/redirects that point to `/admin`
5. Remove admin-specific Filament widgets
6. Delete `app/Filament/Resources/` directory (all moved to System or deleted)

### C5. Move Module Filament Resources to `/system`

Blog, Changelog, Help, Contact, Announcements currently register Filament resources in the System panel via `->when(config('modules.X'))`. These stay as-is — they're already in `/system` for super-admin oversight.

New modules (CRM, HR, etc.) that need super-admin oversight get Filament resources in `/system` too.

---

## Testing Strategy

### Core Tests (`tests/`)
- Auth flows, middleware, services, shared actions
- No module-specific tests here

### Module Tests (`modules/{name}/tests/`)
Each module's test directory:
- **Unit:** Model tests, policy tests, action tests
- **Feature:** Controller tests, page render tests, form submission tests

### Test Commands
```bash
php artisan test --compact                              # core only
php artisan test --compact modules/module-crm/tests/    # single module
php artisan test --compact modules/*/tests/             # all modules
```

### Module Test Independence
- Module tests use their own factories (already in `modules/{name}/database/factories/`)
- Module tests can reference core factories (User, Organization) but not other module factories
- If Module A depends on Module B's models, the test should mock or skip if Module B is disabled

---

## Success Criteria

| Metric | Target |
|--------|--------|
| All 13 modules on ModuleProvider pattern | 13/13 |
| All modules have Inertia CRUD pages | 13/13 |
| All modules have DataTable list views | 13/13 |
| Module tests per module | 15+ for full CRUD modules, 5+ for simple modules |
| `/admin` panel removed | Yes |
| Global resources in `/system` | 5 migrated |
| Sidebar shows/hides based on config | All groups |
| `config('modules.X')` = false disables module completely | Verified |
| Core tests pass without any modules enabled | Yes |

---

## Out of Scope

- Mobile app / PWA
- Module marketplace / dynamic installation
- Cross-module data sharing UI (relationships exist but no unified view)
- Filament v5 custom themes beyond current DESIGN.md alignment
- New billing gateway integrations
- i18n / translations

---

## Estimated Effort

| Phase | Scope | Size |
|-------|-------|------|
| Phase A | ModuleProvider extension + rename CRM/HR + migrate 11 legacy modules + sidebar + test isolation | Large |
| Phase B | 7 module CRUD build-outs (~45 new pages) + move controllers/routes into modules | Large |
| Phase C | Admin panel removal — move 5 resources to /system, delete 3, migrate categories, disable then delete AdminPanelProvider | Medium |

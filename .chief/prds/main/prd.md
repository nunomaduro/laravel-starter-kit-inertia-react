# PRD: Modular Architecture + Puck Report & Dashboard Builders

## Introduction

The starter kit currently bundles product features (blog, changelog, announcements, gamification, help center, contact) directly into the core application. This PRD covers two related initiatives:

1. **Modular Architecture (Part A):** Extract product features into optional, self-contained modules that can be enabled or disabled at install time and runtime. This uses a two-layer system — `config/modules.php` controls install-time availability, while the existing Pennant feature flag system controls per-org/per-user visibility.

2. **Puck Report & Dashboard Builders (Part B):** Extend the existing Puck page builder with two new builder types — a report builder (with charts, tables, KPIs, PDF/CSV export, and scheduled generation) and a dashboard builder (with live-refreshing widgets, activity feeds, and KPI grids). Both are built as modules from day one.

The primary drivers are: enabling customers to pick-and-choose features at install time, achieving cleaner code separation internally, and preparing the architecture for a future plugin/marketplace ecosystem.

## Goals

- Enable install-time module selection so customers only get what they need
- Achieve clean physical code separation with each module self-contained in `modules/{name}/`
- Preserve backward compatibility — disabling a module hides routes and UI but preserves data
- Maintain the existing Pennant feature flag system for runtime per-org/per-user control
- Provide a self-service report builder for all users and admin-managed defaults
- Provide a self-service dashboard builder for all users and admin-managed defaults
- Support full report lifecycle: build, preview, export (PDF/HTML/CSV), and schedule
- Support full dashboard lifecycle: build, set defaults, configure auto-refresh
- All modules follow identical patterns (ServiceProvider, module.json, feature registration)

## User Stories

### US-001: Module config and loader infrastructure
**Priority:** 1
**Description:** As a developer, I need the base module loading infrastructure so that modules can be discovered, registered, and toggled without modifying core application code.

**Acceptance Criteria:**
- [ ] `config/modules.php` exists with a plain array of module names mapped to booleans (no `env()` calls)
- [ ] `ModuleServiceProvider` abstract base class provides `moduleName()`, `featureKey()`, `featureClass()`, `isEnabled()`, route/migration loading, and feature registration
- [ ] `ModuleFeatureRegistry` allows modules to push feature definitions during `register()` instead of merging into config
- [ ] `ModuleLoader::providers()` reads `config/modules.php` and each module's `module.json`, returns enabled provider classes
- [ ] `AppServiceProvider::register()` calls `ModuleLoader::providers()` to register enabled modules
- [ ] `FeatureHelper` queries `ModuleFeatureRegistry` (merged with static config) for `classForKey()`, `keyForClass()`, and `isActiveForKey()`
- [ ] `HandleInertiaRequests` uses `ModuleFeatureRegistry::allInertiaFeatures()` for shared props
- [ ] `php artisan config:cache` works correctly with modules enabled/disabled
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-002: Module artisan commands
**Priority:** 1
**Description:** As a developer, I want artisan commands to list, enable, and disable modules so I can manage modules from the CLI.

**Acceptance Criteria:**
- [ ] `php artisan module:list` displays a table with Name, Label, Status (enabled/disabled), and Description (read from `module.json`)
- [ ] `php artisan module:enable {name}` validates the module exists, sets it to `true` in `config/modules.php` on disk, clears config cache, and runs `php artisan migrate`
- [ ] `php artisan module:disable {name}` warns about existing data, sets module to `false` in `config/modules.php` on disk, clears config cache, does NOT rollback migrations
- [ ] Invalid module name shows a helpful error message
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-003: Stub module validation (Step 0)
**Priority:** 1
**Description:** As a developer, I need to validate the module loading pipeline with a minimal stub before extracting real code, so I can catch infrastructure issues early.

**Acceptance Criteria:**
- [ ] A stub `modules/contact/module.json` and `modules/contact/src/ContactServiceProvider.php` exist with a no-op provider registering one test route (`/module-test`)
- [ ] With contact enabled in config: `/module-test` appears in `php artisan route:list`
- [ ] With contact disabled in config: `/module-test` disappears after `config:clear`
- [ ] `module:enable contact` / `module:disable contact` correctly toggle the route
- [ ] `composer.json` has `"Modules\\Contact\\": "modules/contact/src/"` in autoload
- [ ] After validation, the stub route is removed and real extraction begins
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-004: Extract Contact module
**Priority:** 2
**Description:** As a developer, I want the contact feature extracted into a self-contained module so it can be enabled/disabled independently.

**Acceptance Criteria:**
- [ ] `ContactSubmissionController`, `ContactSubmission` model, `ContactFeature`, Filament resource, factory, and seeder are moved to `modules/contact/src/` and `modules/contact/database/`
- [ ] Contact routes extracted from `routes/web.php` to `modules/contact/routes/web.php`
- [ ] `ContactServiceProvider` extends `ModuleServiceProvider`, registers feature via `ModuleFeatureRegistry`
- [ ] `module.json` includes `seeders` array listing `ContactSubmissionSeeder`
- [ ] All `use App\Models\ContactSubmission` imports updated to `Modules\Contact\Models\ContactSubmission` across codebase
- [ ] Factory `$model` property updated to new namespace
- [ ] With module disabled: contact routes return 404, Filament resource hidden, feature not in Inertia shared props
- [ ] With module enabled: all contact functionality works as before
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-005: Extract Help module
**Priority:** 2
**Description:** As a developer, I want the help center feature extracted into a self-contained module.

**Acceptance Criteria:**
- [ ] `HelpCenterController`, `RateHelpArticleController`, `HelpArticle` model, `RateHelpArticleAction`, `HelpFeature`, Filament resource, factory, and seeder moved to `modules/help/`
- [ ] Help routes extracted to `modules/help/routes/web.php`
- [ ] `HelpArticle` still uses `Categorizable` trait from core (`App\Models\Concerns\Categorizable`)
- [ ] `HelpServiceProvider` registers feature via `ModuleFeatureRegistry`
- [ ] All imports updated across codebase
- [ ] Module enable/disable works correctly
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-006: Extract Changelog module
**Priority:** 2
**Description:** As a developer, I want the changelog feature extracted into a self-contained module.

**Acceptance Criteria:**
- [ ] `ChangelogController`, `ChangelogEntry` model, `ChangelogFeature`, Filament resource, factory, and seeder moved to `modules/changelog/`
- [ ] Changelog routes extracted to `modules/changelog/routes/web.php`
- [ ] `ChangelogServiceProvider` registers feature via `ModuleFeatureRegistry`
- [ ] All imports updated across codebase
- [ ] Module enable/disable works correctly
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-007: Extract Announcements module
**Priority:** 2
**Description:** As a developer, I want the announcements feature extracted into a self-contained module with a proper feature class.

**Acceptance Criteria:**
- [ ] `AnnouncementsTableController`, `Announcement` model, `AnnouncementDataTable`, Filament resource, and seeder moved to `modules/announcements/`
- [ ] New `AnnouncementsFeature` class created in module (replaces old `'welcome-feature-announcements'` string flag)
- [ ] `AnnouncementsServiceProvider` registers feature, DataTable, and the old Pennant flag definition (removed from `AppServiceProvider`)
- [ ] Shared props and welcome UI updated to use `AnnouncementsFeature` class
- [ ] All imports updated across codebase
- [ ] Module enable/disable works correctly
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-008: Extract Blog module
**Priority:** 2
**Description:** As a developer, I want the blog feature extracted into a self-contained module.

**Acceptance Criteria:**
- [ ] `BlogController`, `PostsTableController`, `Post` model, `PostDataTable`, `BlogFeature`, Filament resource, factory, and seeder moved to `modules/blog/`
- [ ] Blog routes extracted to `modules/blog/routes/web.php`
- [ ] `Category` model, `CategoryDataTable`, and `CategoriesTableController` remain in core
- [ ] `BlogServiceProvider` registers feature and DataTable (removed from `AppServiceProvider`)
- [ ] All imports updated across codebase
- [ ] Module enable/disable works correctly
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-009: Extract Gamification module
**Priority:** 2
**Description:** As a developer, I want the gamification feature extracted into a self-contained module with its event listener self-contained.

**Acceptance Criteria:**
- [ ] `GamificationFeature` and `GrantGamificationOnUserCreated` listener moved to `modules/gamification/`
- [ ] Gamification routes extracted to `modules/gamification/routes/web.php`
- [ ] `GrantGamificationOnUserCreated` event registration removed from `AppServiceProvider` — only `GamificationServiceProvider` registers it in `boot()` when enabled
- [ ] `GiveExperience` and `HasAchievements` traits remain on User model (package traits, harmless when disabled)
- [ ] All imports updated across codebase
- [ ] Module enable/disable works correctly — listener only fires when enabled
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-010: Clean up feature flags config
**Priority:** 3
**Description:** As a developer, I want to remove module-specific entries from `config/feature-flags.php` since they are now self-registered via `ModuleFeatureRegistry`.

**Acceptance Criteria:**
- [ ] Blog, changelog, help, contact, gamification entries removed from `inertia_features`, `route_feature_map`, and `feature_metadata` in `config/feature-flags.php`
- [ ] Non-module features remain: cookie_consent, two_factor_auth, impersonation, onboarding, registration, appearance_settings, profile_pdf_export, personal_data_export, scramble_api_docs, component_showcase
- [ ] All feature flag checks still work via `ModuleFeatureRegistry` for module features
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-011: Update cross-module imports
**Priority:** 3
**Description:** As a developer, I need all references to moved classes updated across the codebase so nothing breaks.

**Acceptance Criteria:**
- [ ] `SearchController` updated with module model namespaces (`Post`, `HelpArticle`, `ChangelogEntry`)
- [ ] `PageGalleryController` updated with moved controller paths
- [ ] All seeders in `database/seeders/Development/` updated with module model namespaces
- [ ] All tests updated with module model AND feature class namespaces
- [ ] All factories updated with `$model` property pointing to module namespace
- [ ] All other models with relationships to module models updated
- [ ] No remaining references to old `App\Models\{ModuleModel}` or `App\Features\{ModuleFeature}` namespaces
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-012: Module-aware installer
**Priority:** 3
**Description:** As a user running the installer, I want to choose which modules to enable so I only get the features I need.

**Acceptance Criteria:**
- [ ] `AppInstallCommand` has a new `PHASE_MODULES` step after `PHASE_FEATURES`
- [ ] Uses `multiselect()` from Laravel Prompts to let user pick modules
- [ ] Writes selected modules to `config/modules.php` on disk
- [ ] Runs `config:clear` after writing
- [ ] Only seeders for enabled modules are run during `PHASE_DEMO` (reads `module.json` `seeders` array)
- [ ] `AppUpgradeCommand` also respects module-aware seeder execution
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-013: Module contract test
**Priority:** 3
**Description:** As a developer, I need an automated test that verifies the module toggle contract so regressions are caught on every build.

**Acceptance Criteria:**
- [ ] `tests/Feature/ModuleToggleTest.php` exists
- [ ] Test verifies: disabled module routes return 404
- [ ] Test verifies: disabled module features are NOT in Inertia shared props
- [ ] Test verifies: enabled module routes work and features ARE in shared props
- [ ] Test covers at least one module (contact)
- [ ] Test passes when run directly: `php artisan test --compact tests/Feature/ModuleToggleTest.php`
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-014: Wayfinder regeneration and frontend fixes
**Priority:** 3
**Description:** As a developer, I need frontend route imports to work after modules are extracted so the UI doesn't break.

**Acceptance Criteria:**
- [ ] `npm run build` completes without errors after module extraction
- [ ] Wayfinder regenerates TypeScript route functions for module routes
- [ ] All frontend imports in `@/actions/` and `@/routes/` resolve correctly
- [ ] No broken links or missing route references in React pages
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-015: Report Builder — model, CRUD, and basic blocks
**Priority:** 4
**Description:** As a user, I want to create reports using a drag-and-drop builder so I can visualize my data without writing code.

**Acceptance Criteria:**
- [ ] `Report` model exists with `puck_json`, `organization_id`, `schedule` (nullable cron string), `output_format` (pdf/html/csv enum), standard timestamps
- [ ] Migration creates `reports` table with proper indexes
- [ ] `ReportController` provides full CRUD (index, create, store, show, edit, update, destroy)
- [ ] Report editor uses Puck with report-specific block library
- [ ] `ReportDataSourceRegistry` allows registering query-based data sources (DB queries, aggregations)
- [ ] Routes under `tenant` middleware, feature-gated via `ReportsFeature`
- [ ] Built as a module in `modules/reports/` with `module.json`, `ReportsServiceProvider`
- [ ] Added to `config/modules.php` and `composer.json` autoload
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-016: Report Builder — report-specific blocks
**Priority:** 4
**Description:** As a user, I want specialized blocks for reports (tables, charts, KPIs, filters, summaries) so I can present data effectively.

**Acceptance Criteria:**
- [ ] `TableBlock` renders a data table with sorting and filtering, bound to a data source
- [ ] `ChartBlock` renders bar, line, and pie charts using Recharts, bound to a data source
- [ ] `KpiCard` renders a single metric with trend indicator (up/down/neutral)
- [ ] `FilterBlock` provides date range pickers and dropdowns for report parameters
- [ ] `SummaryBlock` renders text with template variables resolved from data sources
- [ ] All blocks are in `resources/js/components/puck-blocks/reports/`
- [ ] Blocks appear only in the report builder Puck config, not in page builder
- [ ] Base blocks (Heading, Text) are shared and available in the report builder too
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-017: Report Builder — export and scheduling
**Priority:** 5
**Description:** As a user, I want to export reports as PDF, HTML, or CSV and optionally schedule them to run automatically.

**Acceptance Criteria:**
- [ ] Export action on report show page with format selector (PDF/HTML/CSV)
- [ ] PDF export renders the report's Puck layout to a downloadable PDF
- [ ] HTML export renders a standalone HTML file
- [ ] CSV export flattens tabular data sources into a downloadable CSV
- [ ] Schedule field accepts a cron expression (validated)
- [ ] Scheduled reports run via a queued job and store the output for download
- [ ] Users can view and download past scheduled report outputs
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-018: Dashboard Builder — model, CRUD, and basic blocks
**Priority:** 4
**Description:** As a user, I want to create custom dashboards with drag-and-drop widgets so I can monitor key metrics at a glance.

**Acceptance Criteria:**
- [ ] `Dashboard` model exists with `puck_json`, `organization_id`, `is_default` (boolean), `refresh_interval` (nullable integer, seconds)
- [ ] Migration creates `dashboards` table with proper indexes
- [ ] `DashboardController` provides full CRUD plus `set-default` action
- [ ] Dashboard editor uses Puck with dashboard-specific block library
- [ ] `DashboardDataSourceRegistry` allows registering real-time data sources (live queries, cached aggregations, external API polling)
- [ ] Routes under `tenant` middleware, feature-gated via `DashboardsFeature`
- [ ] Built as a module in `modules/dashboards/` with `module.json`, `DashboardsServiceProvider`
- [ ] Added to `config/modules.php` and `composer.json` autoload
- [ ] Only one dashboard per org can be `is_default = true`
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-019: Dashboard Builder — dashboard-specific blocks
**Priority:** 4
**Description:** As a user, I want specialized dashboard widgets (live charts, KPI grids, activity feeds, maps, embeds) so I can build rich monitoring views.

**Acceptance Criteria:**
- [ ] `LiveChartBlock` renders an auto-refreshing chart bound to a data source
- [ ] `KpiGridBlock` renders a responsive grid of KPI cards
- [ ] `ActivityFeedBlock` renders a recent activity stream
- [ ] `MapBlock` renders geographic data visualization (for fleet/real estate use cases)
- [ ] `WidgetBlock` renders an embeddable iframe/component container
- [ ] All blocks are in `resources/js/components/puck-blocks/dashboards/`
- [ ] Blocks appear only in the dashboard builder Puck config
- [ ] Base blocks (Heading, Text) are shared and available in the dashboard builder
- [ ] Auto-refresh respects `refresh_interval` from the Dashboard model
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-020: Puck config factory for multi-builder support
**Priority:** 4
**Description:** As a developer, I need a shared Puck config factory so all three builders (pages, reports, dashboards) share base infrastructure while using different block libraries.

**Acceptance Criteria:**
- [ ] `resources/js/lib/puck-config-factory.ts` exists and accepts a builder type (`page` | `report` | `dashboard`)
- [ ] Returns appropriate Puck config: blocks, categories, data sources for each builder type
- [ ] Base blocks (Heading, Text) are included in all builder configs
- [ ] Builder-specific blocks are only included in their respective configs
- [ ] Existing page builder updated to use the config factory (no behavior change)
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-021: Filament resource discovery for modules
**Priority:** 3
**Description:** As an admin, I want module Filament resources to appear/disappear in the admin panel based on module status.

**Acceptance Criteria:**
- [ ] Each module's ServiceProvider registers its Filament resources via `Filament::serving()` callback
- [ ] No changes needed to `AdminPanelProvider` — modules are self-contained
- [ ] Disabled module's Filament resources do not appear in admin navigation
- [ ] Enabled module's Filament resources appear and function normally
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

### US-022: Documentation and manifest sync
**Priority:** 5
**Description:** As a developer, I need documentation updated and manifest synced after the modular refactor.

**Acceptance Criteria:**
- [ ] `php artisan docs:sync` runs successfully after refactor
- [ ] Each module has a `CLAUDE.md` file describing its purpose and contents
- [ ] Module-related developer docs updated with new paths
- [ ] Manifest reflects new file locations
- [ ] Typecheck passes on changed files (`phpstan analyse` scoped to changed paths); run `pint --dirty --format agent`

## Functional Requirements

- FR-1: `config/modules.php` is a plain PHP array (no `env()` calls). Only the installer, `module:enable`, and `module:disable` write this file.
- FR-2: When a module is disabled in config, its ServiceProvider does not boot, its feature class is never registered, `FeatureHelper` returns false, routes are not loaded, and Filament resources are not discovered.
- FR-3: `ModuleFeatureRegistry` is a static registry that modules push feature definitions into during `register()`. `FeatureHelper` and `HandleInertiaRequests` query this registry merged with static config.
- FR-4: Each module has a `module.json` manifest declaring its provider class, feature key, seeder classes, and dependencies.
- FR-5: Disabling a module does NOT rollback migrations — data is preserved but hidden.
- FR-6: The installer (`AppInstallCommand`) presents a `multiselect()` prompt for module selection during `PHASE_MODULES`.
- FR-7: Seeders are module-aware: core reads each enabled module's `module.json` `seeders` array. No central seeder map in core.
- FR-8: Shared infrastructure (`Category` model, `Categorizable` trait, `CategoryDataTable`, `CategoriesTableController`) stays in core `app/`.
- FR-9: React pages stay in `resources/js/pages/{name}/` — no Vite alias changes.
- FR-10: Existing migrations stay in `database/migrations/` — only NEW module migrations go in module directories.
- FR-11: Report builder supports three output formats: PDF, HTML, CSV.
- FR-12: Report builder supports cron-based scheduling with queued job execution.
- FR-13: Dashboard builder supports per-dashboard `refresh_interval` for auto-refreshing data.
- FR-14: Only one dashboard per organization can be set as default (`is_default = true`).
- FR-15: All three Puck builders (pages, reports, dashboards) share base blocks (Heading, Text) via a config factory.
- FR-16: Report and Dashboard builders are implemented as modules from day one, following the same patterns as extracted modules.

## Non-Goals

- No migration rollback on module disable — data is always preserved
- No automatic priority assignment or dependency resolution between modules (dependencies are documented but not enforced at runtime)
- No hot-reload of modules — enable/disable requires config clear
- No marketplace or remote module installation (future consideration)
- No module versioning or upgrade paths between module versions
- No per-user module toggling — modules are system-wide; per-user control stays with Pennant feature flags
- No custom block creation UI — blocks are developer-authored React components
- No real-time collaborative editing in report/dashboard builders
- No AI-powered report generation or natural language query support (future consideration)

## Design Considerations

- Reuse existing Puck page builder UI patterns for report and dashboard editors
- Report/dashboard list pages should follow the same layout as existing admin pages
- Chart blocks should use Recharts (already installed)
- KPI cards should use consistent styling with existing dashboard components
- Filter blocks should use existing form components (date pickers, selects)
- Module enable/disable should have clear visual feedback in `module:list` output

## Technical Considerations

- `config/modules.php` must work with `config:cache` — no runtime config merging, use `ModuleFeatureRegistry` instead
- `composer.json` PSR-4 autoload entries are required for all modules — `composer dump-autoload` after changes
- Wayfinder must be regenerated after route extraction (`npm run build`)
- `SearchController` references module models with feature flag guards — disabled modules won't break search
- Gamification package traits (`GiveExperience`, `HasAchievements`) on User model are harmless when module disabled
- Report PDF export may require a headless browser or PDF library (e.g., Browsershot or DomPDF)
- Dashboard `refresh_interval` polling should be configurable with minimum and maximum bounds in `config/dashboards.php`
- `ReportDataSourceRegistry` and `DashboardDataSourceRegistry` follow the same pattern as existing `PageDataSourceRegistry`

### Testing Strategy (performance)

- **Per-story verification:** Run only the relevant tests for the story being implemented, not the full suite. Use `php artisan test --compact --filter=TestName` or pass specific file paths (e.g., `php artisan test --compact tests/Feature/ModuleToggleTest.php`).
- **Typecheck:** Run `./vendor/bin/phpstan analyse` scoped to changed files/directories where possible (e.g., `./vendor/bin/phpstan analyse modules/contact/src/`).
- **Lint:** Run `vendor/bin/pint --dirty --format agent` (only changed files).
- **Skip Browser tests:** Never run the Browser test suite during story implementation. Use `--testsuite=Unit --testsuite=Feature` if running broader than a single filter.
- **Full suite gate:** Run the full Unit + Feature suite only once per completed priority tier (after all stories in that priority are done), not after every individual story.

## Success Metrics

- All 6 product features extractable as modules with zero behavior change when enabled
- `module:disable {name}` hides all traces of a module (routes, admin nav, Inertia props) in under 5 seconds
- `module:enable {name}` restores full functionality including running pending migrations
- Installer module selection adds less than 10 seconds to install flow
- Targeted tests pass for each story (`php artisan test --compact --filter=RelevantTest` or by file path); full suite (`php artisan test --compact --testsuite=Unit --testsuite=Feature`) passes at the end of each priority tier
- Config cache (`php artisan config:cache`) works correctly with modules
- Users can create a basic report with at least one chart and one table in under 5 minutes
- Users can create a dashboard with at least 3 widgets in under 5 minutes
- Report export (PDF/HTML/CSV) completes in under 30 seconds for typical reports
- Dashboard auto-refresh works without page reload

## Open Questions

- Should `module:disable` require a `--force` flag to prevent accidental disabling in production?
- Should modules support their own config files (e.g., `config/modules/blog.php`) or keep config minimal in `module.json`?
- For the report builder, which PDF library should be used — DomPDF (simpler) or Browsershot (higher fidelity)?
- Should scheduled reports support email delivery, or only in-app download?
- Should the dashboard builder support public/shared dashboards (accessible without auth)?
- Should there be a `module:make {name}` scaffolding command for creating new modules?
- How should module-specific tests be organized — in `modules/{name}/tests/` or in the central `tests/` directory?

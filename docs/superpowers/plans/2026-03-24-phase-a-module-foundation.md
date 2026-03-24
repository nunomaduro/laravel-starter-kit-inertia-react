# Phase A: Module Foundation & Standardization — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Standardize all 13 modules on the unified `ModuleProvider` pattern with hierarchical feature flags, dynamic sidebar navigation, test isolation, and a `make:module` scaffolding command.

**Architecture:** Extend the existing `ModuleProvider` base class to absorb all capabilities of the legacy `ModuleServiceProvider` (route loading, feature registration, enable/disable toggle, DataTable registration). Then migrate all 11 legacy modules to the new pattern. CRM/HR already use `ModuleProvider` but get renamed for consistency. The sidebar becomes data-driven via a navigation registry.

**Tech Stack:** Laravel 13, PHP 8.4, Pest 4, Inertia v2, React 19, TypeScript

**Spec:** `docs/superpowers/specs/2026-03-24-module-buildout-design.md`
**Depends on:** Codebase hardening (completed)

---

## Task Overview

| # | Task | Files | Scope |
|---|------|-------|-------|
| 1 | Extend ModuleProvider base class | 1 modify, 1 create | Add routes, features, enable/disable, DataTable, nav registry |
| 2 | Create ModuleNavigationRegistry | 1 create | Collect nav items from modules, expose to Inertia |
| 3 | Update HandleInertiaRequests | 1 modify | Share module nav items with frontend |
| 4 | Rename CRM/HR modules | ~20 files | `module-crm/` → `crm/`, `module-hr/` → `hr/` |
| 5 | Register CRM/HR in config + create module.json | 3 modify, 2 create | config/modules.php + manifests |
| 6 | Migrate Blog module | ~5 files | Legacy → ModuleProvider |
| 7 | Migrate Changelog module | ~5 files | Legacy → ModuleProvider |
| 8 | Migrate Help module | ~5 files | Legacy → ModuleProvider |
| 9 | Migrate Contact module | ~5 files | Legacy → ModuleProvider |
| 10 | Migrate Announcements module | ~5 files | Legacy → ModuleProvider |
| 11 | Migrate Billing module | ~5 files | Legacy → ModuleProvider |
| 12 | Migrate Page Builder module | ~5 files | Legacy → ModuleProvider |
| 13 | Migrate Dashboards module | ~5 files | Legacy → ModuleProvider |
| 14 | Migrate Reports module | ~5 files | Legacy → ModuleProvider |
| 15 | Migrate Gamification module | ~5 files | Legacy → ModuleProvider |
| 16 | Migrate Workflows module | ~5 files | Legacy → ModuleProvider |
| 17 | Update CRM/HR providers for new base | ~2 files | Adapt to extended ModuleProvider |
| 18 | Dynamic sidebar navigation | 2 modify, 1 create | app-sidebar.tsx reads nav registry |
| 19 | Module test isolation | 3 modify | Pest.php, phpunit.xml, composer.json scripts |
| 20 | `make:module` Artisan command | 1 create, 1 modify | Scaffolds 18 files |
| 21 | Integration tests | 3 create | Verify feature cascade, sidebar, module toggle |
| 22 | Final verification | 0 | Run all tests, verify sidebar, verify enable/disable |

---

### Task 1: Extend ModuleProvider Base Class

**Files:**
- Modify: `app/Modules/Support/ModuleProvider.php`
- Create: `app/Modules/Support/ModuleFeature.php` (base feature class for modules)

**Context:** The current `ModuleProvider` only handles migrations, model registry, and AI context. We need to add: `isEnabled()`, route loading, feature registration (via `ModuleFeatureRegistry`), DataTable support, and navigation registration. These capabilities currently live in the legacy `ModuleServiceProvider` at `app/Support/ModuleServiceProvider.php`.

- [ ] **Step 1: Read both base classes**

Read: `app/Modules/Support/ModuleProvider.php` and `app/Support/ModuleServiceProvider.php` to understand the full API.

- [ ] **Step 2: Add `isEnabled()` check and feature key to ModuleProvider**

The manifest already has `name`. Derive the module config key from it. Add an `isEnabled()` method and guard `register()` and `boot()` with it.

```php
// In ModuleProvider, replace the current register() and boot():

public function moduleKey(): string
{
    return mb_strtolower($this->manifest()->name);
}

final public function isEnabled(): bool
{
    return (bool) config("modules.{$this->moduleKey()}", false);
}

final public function register(): void
{
    if (! $this->isEnabled()) {
        return;
    }

    $this->registerModels();
    $this->registerFeature();
    $this->registerModule();
}

final public function boot(): void
{
    if (! $this->isEnabled()) {
        return;
    }

    $this->loadMigrations();
    $this->loadRoutes();
    $this->registerAIContext();
    $this->registerRelationships();
    $this->registerNavigation();
    $this->bootModule();
}
```

- [ ] **Step 3: Add route loading**

```php
protected function loadRoutes(): void
{
    $routesPath = $this->moduleBasePath() . '/routes/web.php';
    if (file_exists($routesPath)) {
        Route::middleware('web')->group($routesPath);
    }
}
```

- [ ] **Step 4: Add feature registration**

```php
protected function registerFeature(): void
{
    $key = $this->moduleKey();
    $featureClass = $this->featureClass();

    if ($featureClass !== null) {
        ModuleFeatureRegistry::registerInertiaFeature($key, $featureClass);
        ModuleFeatureRegistry::registerRouteFeature($key, $featureClass);
        ModuleFeatureRegistry::registerFeatureMetadata($key, $this->featureMetadata());
    }
}

/**
 * The Pennant feature class for this module. Return null if no feature gating needed.
 */
protected function featureClass(): ?string
{
    return null;
}

/**
 * Feature metadata for delegation and plan gating.
 */
protected function featureMetadata(): array
{
    return ['delegate_to_orgs' => true, 'plan_required' => null];
}
```

- [ ] **Step 5: Add navigation registration**

```php
protected function registerNavigation(): void
{
    $manifest = $this->manifest();
    if (! empty($manifest->navigation)) {
        ModuleNavigationRegistry::registerGroup($this->moduleKey(), $manifest->navigation);
    }
}
```

- [ ] **Step 6: Add `registerModule()` hook (empty, for subclasses)**

```php
protected function registerModule(): void
{
    //
}
```

- [ ] **Step 7: Extract model registration from old register()**

Move the model registry logic to a private method `registerModels()` called from the new `register()`.

- [ ] **Step 8: Add helper methods from legacy provider**

```php
protected function moduleBasePath(): string
{
    $reflection = new ReflectionClass(static::class);
    return dirname((string) $reflection->getFileName(), 3);
    // 3 levels up: src/Providers/Provider.php → module root
    // Subclasses can override if their structure differs
}

protected function moduleSourcePath(string $path = ''): string
{
    $base = $this->moduleBasePath() . '/src';
    return $path !== '' ? $base . '/' . $path : $base;
}

protected function modulePath(string $path = ''): string
{
    $base = $this->moduleBasePath();
    return $path !== '' ? $base . '/' . $path : $base;
}
```

- [ ] **Step 9: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 10: Commit**

```bash
git add app/Modules/Support/ModuleProvider.php
git commit -m "feat: extend ModuleProvider with routes, features, nav, and enable/disable toggle"
```

---

### Task 2: Create ModuleNavigationRegistry

**Files:**
- Create: `app/Support/ModuleNavigationRegistry.php`

**Context:** Singleton that collects navigation items from modules during boot. The Inertia middleware reads this to share module nav groups with the frontend.

- [ ] **Step 1: Create the registry**

```php
<?php

declare(strict_types=1);

namespace App\Support;

final class ModuleNavigationRegistry
{
    /** @var array<string, array<int, array{label: string, route: string, icon: string, group: string, permission?: string}>> */
    private static array $groups = [];

    /**
     * @param  array<int, array{label: string, route: string, icon: string, group: string, permission?: string}>  $navItems
     */
    public static function registerGroup(string $moduleKey, array $navItems): void
    {
        self::$groups[$moduleKey] = $navItems;
    }

    /**
     * @return array<string, array<int, array{label: string, route: string, icon: string, group: string, permission?: string}>>
     */
    public static function allGroups(): array
    {
        return self::$groups;
    }

    /**
     * Get nav items grouped by their 'group' key across all modules.
     *
     * @return array<string, array<int, array{label: string, route: string, icon: string, module: string, permission?: string}>>
     */
    public static function groupedBySection(): array
    {
        $sections = [];

        foreach (self::$groups as $moduleKey => $items) {
            foreach ($items as $item) {
                $group = $item['group'] ?? $moduleKey;
                $item['module'] = $moduleKey;
                $sections[$group][] = $item;
            }
        }

        return $sections;
    }

    public static function flush(): void
    {
        self::$groups = [];
    }
}
```

- [ ] **Step 2: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Support/ModuleNavigationRegistry.php
git commit -m "feat: add ModuleNavigationRegistry for dynamic sidebar nav"
```

---

### Task 3: Update HandleInertiaRequests to Share Module Nav

**Files:**
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`

- [ ] **Step 1: Read the current share() method**

Find the section where features are shared and add module nav items.

- [ ] **Step 2: Add module nav items to shared props**

In the `share()` method's return array, add:
```php
'moduleNavItems' => fn () => ModuleNavigationRegistry::groupedBySection(),
```

Import: `use App\Support\ModuleNavigationRegistry;`

- [ ] **Step 3: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Middleware/HandleInertiaRequests.php
git commit -m "feat: share module navigation items with Inertia frontend"
```

---

### Task 4: Rename CRM/HR Module Directories

**Context:** `module-crm` and `module-hr` use a different naming convention than all other modules. Rename for consistency.

- [ ] **Step 1: Rename directories**

```bash
mv modules/module-crm modules/crm
mv modules/module-hr modules/hr
```

- [ ] **Step 2: Update all namespace references**

For CRM: find-and-replace `Cogneiss\ModuleCrm` → `Modules\Crm` (or keep the existing namespace — check what pattern other modules use). Update:
- `modules/crm/composer.json` — PSR-4 autoload
- `modules/crm/src/Providers/CrmModuleServiceProvider.php` — namespace
- All model files, policy files, factory files, controller files
- `routes/web.php` references
- `app/Providers/Filament/SystemPanelProvider.php` — resource discovery paths
- Test files

Same for HR.

- [ ] **Step 3: Run composer dump-autoload**

```bash
composer dump-autoload
```

- [ ] **Step 4: Run full test suite to verify nothing broke**

```bash
php artisan test --compact
```

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "refactor: rename module-crm → crm, module-hr → hr for consistency"
```

---

### Task 5: Register CRM/HR in config/modules.php + Create module.json

**Files:**
- Modify: `config/modules.php`
- Create: `modules/crm/module.json`
- Create: `modules/hr/module.json`

- [ ] **Step 1: Add to config/modules.php**

```php
'crm' => true,
'hr' => true,
```

- [ ] **Step 2: Create module.json for CRM**

```json
{
    "name": "crm",
    "label": "CRM",
    "description": "Customer relationship management with contacts, deals, pipelines, and activity tracking.",
    "provider": "Modules\\Crm\\Providers\\CrmModuleServiceProvider"
}
```

- [ ] **Step 3: Create module.json for HR**

```json
{
    "name": "hr",
    "label": "HR",
    "description": "Human resources with employee management, departments, and leave tracking.",
    "provider": "Modules\\Hr\\Providers\\HrModuleServiceProvider"
}
```

- [ ] **Step 4: Verify ModuleLoader discovers them**

```bash
php artisan tinker --execute "var_dump(App\Support\ModuleLoader::providers());"
```

- [ ] **Step 5: Commit**

```bash
git add config/modules.php modules/crm/module.json modules/hr/module.json
git commit -m "feat: register CRM and HR in module system with module.json manifests"
```

---

### Tasks 6-16: Migrate Legacy Modules to ModuleProvider

Each of these tasks follows the same pattern. I'll document Task 6 (Blog) in detail as the reference, then the remaining tasks follow the same steps.

### Task 6: Migrate Blog Module

**Files:**
- Create: `modules/blog/src/Providers/BlogModuleServiceProvider.php`
- Modify: `modules/blog/module.json` — update provider reference
- Modify: `modules/blog/composer.json` — update autoload if needed
- Delete: `modules/blog/src/BlogServiceProvider.php` (old provider)

**Context:** Blog currently extends legacy `ModuleServiceProvider`. It has: `moduleName()=blog`, `featureKey()=blog`, `featureClass()=BlogFeature::class`. It also registers policies and DataTable controllers in `bootModule()`.

- [ ] **Step 1: Read the current BlogServiceProvider**

Read `modules/blog/src/BlogServiceProvider.php` to understand all custom logic.

- [ ] **Step 2: Create new BlogModuleServiceProvider extending ModuleProvider**

```php
<?php

declare(strict_types=1);

namespace Modules\Blog\Providers;

use App\Modules\Contracts\ProvidesAIContext;
use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Blog\Features\BlogFeature;
use Modules\Blog\Models\Post;
use Modules\Blog\Policies\PostPolicy;

final class BlogModuleServiceProvider extends ModuleProvider implements ProvidesAIContext
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'blog',
            version: '1.0.0',
            description: 'Blog posts with categories, tags, and SEO support.',
            models: [Post::class],
            pages: [
                'blog.index' => 'blog/index',
                'blog.show' => 'blog/show',
            ],
            navigation: [
                ['label' => 'Blog', 'route' => 'blog.index', 'icon' => 'file-text', 'group' => 'Content'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return BlogFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        // Copy any DataTable registration from old provider
    }

    public function systemPrompt(): string
    {
        return 'Blog module: manages posts with categories, tags, rich text content, and SEO metadata.';
    }

    public function tools(): array
    {
        return [];
    }

    public function searchableModels(): array
    {
        return [Post::class];
    }
}
```

- [ ] **Step 3: Update module.json**

```json
{
    "name": "blog",
    "label": "Blog",
    "description": "Blog posts with categories, tags, and SEO support.",
    "provider": "Modules\\Blog\\Providers\\BlogModuleServiceProvider"
}
```

- [ ] **Step 4: Create Providers directory if it doesn't exist and update composer.json autoload**

Ensure `modules/blog/composer.json` has the correct PSR-4 mapping.

- [ ] **Step 5: Delete old BlogServiceProvider**

```bash
rm modules/blog/src/BlogServiceProvider.php
```

- [ ] **Step 6: Run Pint and tests**

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact --filter=Blog
```

- [ ] **Step 7: Commit**

```bash
git add modules/blog/
git commit -m "refactor: migrate Blog module to ModuleProvider pattern"
```

---

### Tasks 7-16: Migrate Remaining Modules

Each follows the EXACT same pattern as Task 6. For each module:

1. Read current service provider
2. Create new `{Module}ModuleServiceProvider` extending `ModuleProvider`
3. Implement `manifest()` with models, pages, navigation
4. Implement `featureClass()` if the module has a Pennant feature
5. Move custom `bootModule()` logic (policy registration, DataTable registration)
6. Implement `ProvidesAIContext` if valuable
7. Update `module.json` to point to new provider
8. Delete old provider
9. Run Pint and tests
10. Commit

**Module-specific notes:**

| Task | Module | Feature Class | Custom Boot Logic |
|------|--------|--------------|-------------------|
| 7 | Changelog | `ChangelogFeature` | Policy, DataTable |
| 8 | Help | `HelpFeature` | Policy, DataTable |
| 9 | Contact | `ContactFeature` | Policy |
| 10 | Announcements | `AnnouncementsFeature` | Policy, DataTable |
| 11 | Billing | Check if exists | Policy, many models, complex registration |
| 12 | Page Builder | Check if exists | Policy, Puck integration |
| 13 | Dashboards | Check if exists | Policy |
| 14 | Reports | Check if exists | Policy |
| 15 | Gamification | Check if exists | Widget registration |
| 16 | Workflows | Check if exists | Workflow service registration |

---

### Task 17: Update CRM/HR Providers for Extended ModuleProvider

**Files:**
- Modify: `modules/crm/src/Providers/CrmModuleServiceProvider.php`
- Modify: `modules/hr/src/Providers/HrModuleServiceProvider.php`

**Context:** CRM/HR already extend `ModuleProvider` but don't implement the new methods (featureClass, featureMetadata, routes). They also need navigation items in their manifests.

- [ ] **Step 1: Update CRM provider**

Add to manifest's `navigation`:
```php
navigation: [
    ['label' => 'Contacts', 'route' => 'crm.contacts.index', 'icon' => 'users', 'group' => 'CRM'],
    ['label' => 'Deals', 'route' => 'crm.deals.index', 'icon' => 'trending-up', 'group' => 'CRM'],
    ['label' => 'Pipelines', 'route' => 'crm.pipelines.index', 'icon' => 'git-branch', 'group' => 'CRM'],
],
```

Add `featureClass()` override returning a new `CrmFeature::class`. Create the feature class.

- [ ] **Step 2: Update HR provider**

Same pattern — add navigation items and feature class.

- [ ] **Step 3: Create feature classes**

Create `modules/crm/src/Features/CrmFeature.php` and `modules/hr/src/Features/HrFeature.php` following the Pennant feature pattern.

- [ ] **Step 4: Run Pint and tests**

- [ ] **Step 5: Commit**

---

### Task 18: Dynamic Sidebar Navigation

**Files:**
- Modify: `resources/js/components/app-sidebar.tsx`
- Modify: `resources/js/types/index.d.ts` (add ModuleNavItem type)

**Context:** Replace hardcoded nav items with a mix of core items (Platform, Organization) + dynamic module items from `moduleNavItems` shared prop.

- [ ] **Step 1: Add TypeScript types**

```typescript
export interface ModuleNavItem {
    label: string;
    route: string;
    icon: string;
    module: string;
    permission?: string;
}

export interface SharedData {
    // ... existing props
    moduleNavItems: Record<string, ModuleNavItem[]>;
}
```

- [ ] **Step 2: Update app-sidebar.tsx**

Remove module-specific hardcoded nav items (blog, changelog, HR, CRM, billing, help, etc.) from `mainNavItems`. Keep only core items (Dashboard, Chat, Members, Settings).

Add dynamic rendering of `moduleNavItems`:

```typescript
const { moduleNavItems = {} } = usePage<SharedData>().props;

// Convert module groups to NavItem format
const dynamicNavItems = useMemo(() => {
    return Object.entries(moduleNavItems).flatMap(([group, items]) =>
        items.map((item) => ({
            title: item.label,
            href: route(item.route) ?? '#',
            icon: resolveIcon(item.icon), // map string → Lucide component
            group,
            feature: item.module,
            permission: item.permission,
        }))
    );
}, [moduleNavItems]);

// Merge core + dynamic items for canShowNavItem filtering
const allNavItems = [...coreNavItems, ...dynamicNavItems];
```

- [ ] **Step 3: Create icon resolver**

Map Lucide icon string names to components:
```typescript
import * as Icons from 'lucide-react';

function resolveIcon(name: string): LucideIcon {
    const pascalName = name.split('-').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join('');
    return (Icons as Record<string, LucideIcon>)[pascalName] ?? Icons.Box;
}
```

- [ ] **Step 4: Verify module groups show/hide based on feature flags**

Set `config('modules.blog')` to `false`, reload — Blog nav group should disappear.

- [ ] **Step 5: Run TypeScript check and commit**

```bash
npx tsc --noEmit
git add resources/js/components/app-sidebar.tsx resources/js/types/
git commit -m "feat: dynamic sidebar navigation driven by module registry"
```

---

### Task 19: Module Test Isolation

**Files:**
- Modify: `phpunit.xml`
- Modify: `tests/Pest.php`
- Modify: `composer.json` (scripts section)

- [ ] **Step 1: Add module test suites to phpunit.xml**

Add a `<testsuite>` entry for module tests:
```xml
<testsuite name="Modules">
    <directory>modules/*/tests</directory>
</testsuite>
```

- [ ] **Step 2: Update Pest.php to discover module tests**

Ensure Pest discovers `modules/*/tests/` directories.

- [ ] **Step 3: Add Composer scripts**

```json
{
    "scripts": {
        "test": "php artisan test --compact --testsuite=Unit,Feature",
        "test:modules": "php artisan test --compact --testsuite=Modules",
        "test:all": "php artisan test --compact"
    }
}
```

- [ ] **Step 4: Verify test isolation**

```bash
composer test          # core only
composer test:modules  # module tests only
composer test:all      # everything
```

- [ ] **Step 5: Commit**

```bash
git add phpunit.xml tests/Pest.php composer.json
git commit -m "feat: module test isolation with separate test suites"
```

---

### Task 20: `make:module` Artisan Command

**Files:**
- Create: `app/Console/Commands/MakeModuleCommand.php`

- [ ] **Step 1: Read existing `make:` commands for pattern reference**

Check `app/Console/Commands/` for existing artisan commands to follow the project style.

- [ ] **Step 2: Create the command**

The command should:
1. Accept a module name argument (e.g., `Inventory`)
2. Generate the full directory structure per spec (module.json, composer.json, service provider, model, policy, actions, controller, DataTable, feature class, routes, migration, factory, seeder, 2 test files)
3. Use stubs with variable replacement (similar to Laravel's `make:model --all`)
4. Add entry to `config/modules.php`
5. Run `composer dump-autoload`

Key implementation:
- Create stubs in `stubs/module/` directory
- Command replaces `{{ ModuleName }}`, `{{ moduleName }}`, `{{ module_name }}` etc.
- Total: 18 files generated

- [ ] **Step 3: Create stub files**

Create stubs for each file the command generates (model, controller, provider, etc.)

- [ ] **Step 4: Test the command**

```bash
php artisan make:module TestModule --no-interaction
# Verify: modules/test-module/ exists with all 18 files
# Verify: config/modules.php has 'test-module' => true
# Clean up: rm -rf modules/test-module/
```

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Console/Commands/MakeModuleCommand.php stubs/module/
git commit -m "feat: add make:module artisan command (scaffolds 18 files)"
```

---

### Task 21: Integration Tests

**Files:**
- Create: `tests/Feature/ModuleSystemTest.php`

- [ ] **Step 1: Write tests for the module system**

```php
it('disables module when config is false', function (): void {
    config(['modules.blog' => false]);
    // Verify blog routes are not registered
    // Verify blog feature is not in Inertia shared props
});

it('registers module features in ModuleFeatureRegistry', function (): void {
    // Verify blog, crm, hr etc. are registered
});

it('shares module nav items via Inertia', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page->has('moduleNavItems'));
});

it('make:module command creates all files', function (): void {
    $this->artisan('make:module', ['name' => 'TestScaffold', '--no-interaction' => true])
        ->assertSuccessful();

    expect(is_dir(base_path('modules/test-scaffold')))->toBeTrue();
    // Verify key files exist

    // Cleanup
    File::deleteDirectory(base_path('modules/test-scaffold'));
});
```

- [ ] **Step 2: Run tests**

```bash
php artisan test --compact --filter=ModuleSystem
```

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/ModuleSystemTest.php
git commit -m "test: add integration tests for module system"
```

---

### Task 22: Final Verification

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --compact
```

- [ ] **Step 2: Run Pint on all PHP**

```bash
vendor/bin/pint --format agent
```

- [ ] **Step 3: Run TypeScript check**

```bash
npx tsc --noEmit
```

- [ ] **Step 4: Build frontend**

```bash
npm run build
```

- [ ] **Step 5: Verify in browser**

- All 13 modules appear in sidebar when enabled
- Disabling a module in config hides its nav group
- `make:module` generates correct structure
- Core tests pass without modules
- Module tests run independently

- [ ] **Step 6: Commit any final fixes**

# Modular Architecture

The application uses a modular architecture where product features are extracted into self-contained modules under `modules/`. Modules can be enabled or disabled at install time and runtime.

## Module Structure

Each module lives in `modules/{name}/` and follows this structure:

```
modules/{name}/
├── CLAUDE.md              # Module description and contents
├── module.json            # Metadata: name, label, description, provider, seeders
├── database/
│   ├── factories/         # Model factories
│   ├── migrations/        # Module-specific migrations
│   └── seeders/           # Demo data seeders
├── routes/
│   └── web.php            # Module routes (feature-gated)
└── src/
    ├── {Name}ServiceProvider.php   # Extends ModuleServiceProvider
    ├── Actions/            # Business logic actions
    ├── DataTables/         # Server-side DataTable classes
    ├── Enums/              # Module-specific enums
    ├── Features/           # Pennant feature flag class
    ├── Filament/           # Admin panel resources/widgets
    ├── Http/
    │   ├── Controllers/    # HTTP controllers
    │   └── Requests/       # Form request validation
    ├── Listeners/          # Event listeners
    ├── Models/             # Eloquent models
    ├── Policies/           # Authorization policies
    └── Services/           # Service classes
```

## Available Modules

| Module | Namespace | Description |
|--------|-----------|-------------|
| `announcements` | `Modules\Announcements` | In-app announcement banners with targeting and scheduling |
| `blog` | `Modules\Blog` | Blog posts with categories, tags, and SEO |
| `changelog` | `Modules\Changelog` | Changelog entries and release notes |
| `contact` | `Modules\Contact` | Contact form and submission management |
| `dashboards` | `Modules\Dashboards` | Drag-and-drop dashboards with live widgets |
| `gamification` | `Modules\Gamification` | Badges, points, levels, and achievements |
| `help` | `Modules\Help` | Help center with articles and ratings |
| `reports` | `Modules\Reports` | Report builder with charts, tables, and exports |

## Toggling Modules

### CLI

```bash
php artisan module:list              # Show all modules and status
php artisan module:enable contact    # Enable a module
php artisan module:disable contact   # Disable a module
```

### Config

Modules are toggled in `config/modules.php`:

```php
return [
    'announcements' => true,
    'blog' => true,
    'contact' => true,
    // ...
];
```

### Installer

The `php artisan app:install` command includes a module selection step where users choose which modules to enable.

## How It Works

1. **`ModuleLoader`** reads `config/modules.php` and each module's `module.json` to discover enabled providers.
2. **`AppServiceProvider::register()`** calls `ModuleLoader::providers()` to register enabled modules.
3. Each module's **ServiceProvider** (extending `ModuleServiceProvider`) registers its feature flag via `ModuleFeatureRegistry`, loads routes and migrations, and discovers Filament resources.
4. **`ModuleFeatureRegistry`** replaces static config for feature flag lookups — `FeatureHelper` and `HandleInertiaRequests` query it for active features.
5. When disabled, a module's routes return 404, its Filament resources are hidden, and its feature flag is absent from Inertia shared props.

## Key Infrastructure Classes

- `App\Services\ModuleLoader` — discovers and loads modules
- `App\Providers\ModuleServiceProvider` — abstract base for module providers
- `App\Services\ModuleFeatureRegistry` — central feature flag registry
- `App\Helpers\FeatureHelper` — unified feature flag checks

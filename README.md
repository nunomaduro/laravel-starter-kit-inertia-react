- Inertia & React (this project) version: **[github.com/nunomaduro/laravel-starter-kit-inertia-react](https://github.com/nunomaduro/laravel-starter-kit-inertia-react)**
- Inertia & Vue version: **[github.com/nunomaduro/laravel-starter-kit-inertia-vue](https://github.com/nunomaduro/laravel-starter-kit-inertia-vue)**
- Blade version: **[github.com/nunomaduro/laravel-starter-kit](https://github.com/nunomaduro/laravel-starter-kit)**

<p align="center">
    <a href="https://youtu.be/VhzP0XWGTC4" target="_blank">
        <img src="https://github.com/nunomaduro/laravel-starter-kit/blob/main/art/banner.png" alt="Overview Laravel Starter Kit" style="width:70%;">
    </a>
</p>

<p>
    <a href="https://github.com/nunomaduro/laravel-starter-kit-inertia-react/actions"><img src="https://github.com/nunomaduro/laravel-starter-kit-inertia-react/actions/workflows/tests.yml/badge.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/nunomaduro/laravel-starter-kit-inertia-react"><img src="https://img.shields.io/packagist/dt/nunomaduro/laravel-starter-kit-inertia-react" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/nunomaduro/laravel-starter-kit-inertia-react"><img src="https://img.shields.io/packagist/v/nunomaduro/laravel-starter-kit-inertia-react" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/nunomaduro/laravel-starter-kit-inertia-react"><img src="https://img.shields.io/packagist/l/nunomaduro/laravel-starter-kit-inertia-react" alt="License"></a>
</p>

**Laravel Starter Kit (Inertia & React)** is an ultra-strict, type-safe [Laravel](https://laravel.com) skeleton engineered for developers who refuse to compromise on code quality. This opinionated starter kit enforces rigorous development standards through meticulous tooling configuration and architectural decisions that prioritize type safety, immutability, and fail-fast principles.

## Why This Starter Kit?

Modern PHP has evolved into a mature, type-safe language, yet many Laravel projects still operate with loose conventions and optional typing. This starter kit changes that paradigm by enforcing:

- **Fully Actions-Oriented Architecture**: Every operation is encapsulated in a single-action class
- **Cruddy by Design**: Standardized CRUD operations for all controllers, actions, and Inertia & React pages
- **100% Type Coverage**: Every method, property, and parameter is explicitly typed
- **Zero Tolerance for Code Smells**: Rector, PHPStan, ESLint, and Prettier at maximum strictness catch issues before they become bugs
- **Immutable-First Architecture**: Data structures favor immutability to prevent unexpected mutations
- **Fail-Fast Philosophy**: Errors are caught at compile-time, not runtime
- **Automated Code Quality**: Pre-configured tools ensure consistent, pristine code across your entire team
- **Just Better Laravel Defaults**: Thanks to **[Essentials](https://github.com/nunomaduro/essentials)** / strict models, auto eager loading, immutable dates, and more...
- **AI Guidelines**: Integrated AI Guidelines to assist in maintaining code quality and consistency
- **AI Integration**: Prism PHP (OpenRouter, `ai()` helper) for text, structured output, and MCP/Relay; Laravel AI SDK for agents, embeddings, images, and provider tools. See `docs/developer/backend/prism.md` and `docs/developer/backend/ai-sdk.md`.
- **Optional: PostgreSQL + pgvector**: Vector embeddings for semantic search and RAG when using PostgreSQL
- **Multi-Tenant & Single-Tenant Modes**: Switch between SaaS (multi-org) and internal apps with `MULTI_ORGANIZATION_ENABLED`; see `docs/developer/backend/single-tenant-mode.md`
- **Server-side DataTables**: [machour/laravel-data-table](https://github.com/coding-sunshine/laravel-data-table) (project fork) — one PHP class per model for sorting, filtering, pagination, quick views, and a full React UI (TanStack Table); see `docs/developer/backend/data-table.md`
- **Durable Workflows**: [laravel-workflow](https://github.com/durable-workflow/workflow) for long-running, persistent workflows (sagas, onboarding, AI pipelines); [Waterline](https://github.com/durable-workflow/waterline) UI at `/waterline` for monitoring (admin only). See `docs/developer/backend/durable-workflow.md`
- **Full Testing Suite**: More than 150 tests with 100% code coverage using Pest
- **Automated Seeder System**: Comprehensive seeder automation with category-based organization, JSON support, and relationship-aware generation
- **Activity Logging**: [Spatie Laravel Activity Log](https://spatie.be/docs/laravel-activitylog/v4/introduction) and [Filament Activity Log](https://filamentphp.com/plugins/alizharb-activity-log) for user and model changes, including 2FA and role/permission changes, with IP and user agent; new models from `make:model:full` get activity logging by default
-
This isn't just another Laravel boilerplate—it's a statement that PHP applications can and should be built with the same rigor as strongly-typed languages like Rust or TypeScript.

## Getting Started

> **Requires [PHP 8.4+](https://php.net/releases/), [Bun](https://bun.sh) and a code coverage driver like [xdebug](https://xdebug.org/docs/install)**.

Create your type-safe Laravel application using [Composer](https://getcomposer.org):

```bash
composer create-project nunomaduro/laravel-starter-kit-inertia-react --prefer-dist example-app
```

### Initial Setup

Navigate to your project and complete the setup:

```bash
cd example-app

# Setup the project
composer setup

# Start the development server
composer dev
```

### Optional: Browser Testing Setup

If you plan to use Pest's browser testing capabilities:

```bash
bun add playwright
bunx playwright install
```

### Verify Installation

Run the test suite to ensure everything is configured correctly:

```bash
composer test
```

You should see all tests passing. For the full suite (coverage, type coverage, lint, static analysis), run `composer test:full`.

## Available Tooling

### Development
- `composer dev` - Starts Laravel server, queue worker, log monitoring, and Vite dev server concurrently
- `php artisan seed:environment` - Seed database with environment-aware seeders

### Code Quality
- `composer lint` - Runs Rector (refactoring), Pint (PHP formatting), and Prettier (JS/TS formatting)
- `composer test:lint` - Dry-run mode for CI/CD pipelines

### Testing
- `composer test` - Fast test suite (Pest in parallel, compact output); used by pre-commit
- `composer test:quick` - Alias for the same fast run
- `composer test:full` - Full suite: type coverage, unit tests with coverage, lint, static analysis (use in CI or before release)
- `composer test:type-coverage` - Ensures 100% type coverage with Pest
- `composer test:types` - Runs PHPStan at level 9 (maximum strictness)
- `composer test:unit` - Runs Pest tests with 100% code coverage requirement

### Maintenance
- `composer update:requirements` - Updates all PHP and NPM dependencies to latest versions

### Database Seeding

This starter kit includes a **comprehensive automated seeder system** that ensures all models have corresponding seeders:

- `php artisan make:model:full {name}` - Create model with factory, seeder, and JSON data
- `php artisan seed:environment` - Run environment-aware seeders
- `php artisan seeders:list` - List all available seeders
- `php artisan seeders:sync` - Sync seeders with models
- `php artisan models:audit` - Audit models for missing factories/seeders

#### Quick Start

Create a new model with full setup:
```bash
php artisan make:model:full Post --category=development --all
```

Seed the database:
```bash
php artisan seed:environment
```

Use in tests:
```php
seedFor(Post::class, 5); // Auto-seeds with relationships
```

#### Features

- **Category-based organization**: Essential, Development, Production seeders
- **JSON data support**: Maintainable seed data in JSON files
- **Environment-aware**: Automatically runs appropriate seeders
- **Relationship-aware**: Auto-detects and seeds relationships
- **Git hooks**: Pre-commit validation for new models
- **Full automation**: Commands to create, audit, and sync seeders
- **Seed Specs**: Canonical descriptions that auto-sync with schema changes
- **AI-Assisted**: Offline AI generation of realistic seed data (with graceful fallback)
- **AI-Powered Seeders**: Intelligent seeder code generation using AI + model context
- **Enhanced Relationships**: Full relationship detection using model reflection
- **Auto-Regeneration**: Seeders auto-update when relationships change
- **Auto-Generation**: Smart JSON generation when creating models (AI or Faker)
- **Migration Listener**: Auto-syncs specs and regenerates seeders after migrations
- **Idempotent by Default**: All seeders use updateOrCreate/firstOrCreate patterns
- **Interactive Pre-Commit**: Prompts to auto-fix missing components
- **Test Scenarios**: Named scenarios for consistent test data
- **Real Data Profiling**: Learn from production patterns
- **Observability**: Metrics, logs, and strict/lenient modes
- **AI Review**: Automated review of seeders and specs
- **Structured Output**: Reliable JSON generation using Prism

See [Seeder Documentation](./docs/developer/backend/database/seeders.md) and [Advanced Features](./docs/developer/backend/database/advanced-features.md) for complete details.

### Documentation

This starter kit includes **automated documentation management** to ensure all code is properly documented:

- `composer docs:check` - Check if all Actions, Controllers, and Pages are documented
- `composer docs:sync` - Sync the documentation manifest with the current codebase
- `composer docs:generate` - Auto-generate documentation stubs for undocumented items
- `php artisan docs:sync` - Same as above (Artisan command)
- `php artisan docs:sync --check` - Check-only mode (returns exit code 1 if undocumented items found)
- `php artisan docs:sync --generate` - Generate documentation stubs automatically
- `php artisan docs:sync --generate --ai` - Generate rich AI prompts for full documentation generation (saved under `docs/.ai-prompts/`)
- `php artisan docs:review` - Review documentation quality and detect mismatches with the current code
- `php artisan docs:api` - Regenerate API reference under `docs/developer/api-reference/routes.md`

#### How It Works

The documentation system automatically:

1. **Scans your codebase** for Actions, Controllers, and Pages
2. **Extracts structure & docs** using PHP reflection and PHPDoc/TSDoc (methods, parameters, return types, props)
3. **Discovers relationships** between Actions, Controllers, Routes, Models, and Pages and stores them in the manifest
4. **Tracks documentation status** in `docs/.manifest.json`
5. **Enforces documentation** via:
   - Pre-commit Git hook (blocks commits if documentation is missing)
   - CI/CD checks (fails builds if undocumented items are detected)
   - AI guidelines (requires documentation before marking tasks complete)
6. **Auto-syncs** manifest when you run `composer install` or `composer update`
7. **Auto-updates index files** (Actions/Controllers/Pages READMEs) with documentation status tables

#### Documentation Structure

- **User Guide**: `docs/user-guide/` - End-user facing documentation
- **Developer Guide**: `docs/developer/` - Technical documentation for developers
- **Templates**: `docs/.templates/` - Documentation templates for consistent structure
- **Manifest**: `docs/.manifest.json` - Tracks what's documented vs. what exists
- **AI Prompts**: `docs/.ai-prompts/` - Generated prompts for AI agents to produce full documentation
- **API Reference**: `docs/developer/api-reference/routes.md` - Markdown API reference generated from routes/controllers

#### Quick Start

Check documentation status:
```bash
composer docs:check
```

Generate documentation stubs for undocumented items:
```bash
composer docs:generate
```

#### Automation Features

- **Pre-commit Hook**: Automatically checks documentation before allowing commits
- **CI/CD Integration**: GitHub Actions verifies documentation in all pull requests
- **AI Integration**: Laravel Boost guidelines automatically require documentation for new code
- **Auto-sync**: Manifest automatically syncs when dependencies are installed or updated
- **Template-based**: Consistent documentation structure using templates in `docs/.templates/`

The system will automatically prompt you (or AI agents) to document new features when they're created. Documentation is **mandatory** - commits and CI builds will fail if documentation is missing.

## License

**Laravel Starter Kit Inertia React** was created by **[Nuno Maduro](https://x.com/enunomaduro)** under the **[MIT license](https://opensource.org/licenses/MIT)**.

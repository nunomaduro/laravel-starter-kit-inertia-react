<laravel-boost-guidelines>
=== .ai/app.actions rules ===

# App/Actions guidelines

- This application uses the Action pattern and prefers for much logic to live in reusable and composable Action classes.
- Actions live in `app/Actions`, they are named based on what they do, with no suffix.
- Actions will be called from many different places: jobs, commands, HTTP requests, API requests, MCP requests, and more.
- Create dedicated Action classes for business logic with a single `handle()` method.
- Inject dependencies via constructor using private properties.
- Create new actions with `php artisan make:action "{name}" --no-interaction`
- Wrap complex operations in `DB::transaction()` within actions when multiple models are involved.
- Some actions won't require dependencies via `__construct` and they can use just the `handle()` method.

<!-- Example action class -->
```php
<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class CreateFavorite
{
    public function __construct(private FavoriteService $favorites)
    {
        //
    }

    public function handle(User $user, string $favorite): bool
    {
        return $this->favorites->add($user, $favorite);
    }
}
```

=== .ai/documentation rules ===

# Documentation Guidelines

## CRITICAL: Documentation is MANDATORY

**Documentation is NOT optional.** Every new Action, Controller, Page, or Route MUST be documented before the task is considered complete. The AI agent MUST NOT mark a task as complete if documentation is missing.

## Automatic Documentation Triggers

When completing work involving these paths, documentation updates are **MANDATORY** and **NON-NEGOTIABLE**:

| File Pattern | Documentation Action | Boost Tool to Use |
|--------------|---------------------|-------------------|
| `app/Actions/*.php` (new) | Create action doc | `application-info`, `list-routes` |
| `app/Actions/*.php` (modify) | Update existing action doc | `application-info` |
| `app/Http/Controllers/*.php` (new) | Create controller doc | `list-routes`, `application-info` |
| `resources/js/pages/**/*.tsx` (new) | Document page | `application-info`, `list-routes` |
| `routes/web.php` (new route) | Update route reference | `list-routes` |
| `config/fortify.php` (feature toggle) | Update auth docs | `application-info` |
| `database/migrations/*` (new) | Update schema docs | `database-schema` |

## Using Boost Tools for Documentation

### Before Documenting New Features

1. **Run `application-info`** to get:
   - All Eloquent models and their relationships
   - Installed packages and versions
   - Current application context

2. **Run `database-schema`** (if data-related) to understand:
   - Table structures
   - Foreign key relationships
   - Column types and constraints

3. **Run `list-routes`** to capture:
   - All available endpoints
   - Route parameters
   - Middleware applied

4. **Run `search-docs`** (for Laravel features) to get:
   - Version-specific documentation
   - Best practices and patterns

## Documentation Decision Matrix

| Change Type | User Guide | Developer Guide | API Reference |
|-------------|------------|-----------------|---------------|
| New user-visible feature | ✅ Create | ✅ Create | ✅ Update routes |
| New Action | ❌ Skip | ✅ Create | ❌ Skip |
| New Controller | ❌ Skip | ✅ Create | ✅ Update routes |
| New Page (user-facing) | ✅ Create | ✅ Create | ✅ Update routes |
| Bug fix | ❌ Skip | ❌ Skip | ❌ Skip |
| Refactor (no behavior change) | ❌ Skip | ✅ If architecture changes | ❌ Skip |
| New validation rules | ❌ Skip | ✅ Update Form Request docs | ❌ Skip |
| UI-only changes | ✅ If workflow changes | ❌ Skip | ❌ Skip |

## Documentation Location Matrix

| Component Type | User Docs Location | Developer Docs Location |
|---------------|-------------------|-------------------------|
| Authentication | `docs/user-guide/authentication/` | `docs/developer/backend/auth/` |
| User Settings | `docs/user-guide/account/` | `docs/developer/backend/controllers/` |
| Actions | N/A | `docs/developer/backend/actions/` |
| Pages | `docs/user-guide/` (if user-facing) | `docs/developer/frontend/pages/` |
| Components | N/A | `docs/developer/frontend/components/` |
| Routes | N/A | `docs/developer/api-reference/routes.md` |

## Documentation Templates

Templates are available in `docs/.templates/`:

- `action.md` - For documenting Actions
- `controller.md` - For documenting Controllers
- `page.md` - For documenting Inertia pages
- `user-feature.md` - For user-facing documentation

Use these templates to ensure consistent documentation structure.

## Manifest Tracking

After creating or updating documentation:

1. Update `docs/.manifest.json` with:
   - `"documented": true`
   - `"path"`: Relative path to documentation file
   - `"lastUpdated"`: Current date (YYYY-MM-DD format)

2. Update relevant index files (e.g., `docs/developer/backend/actions/README.md`)

## MANDATORY Self-Check Before Completing Tasks

**YOU MUST VERIFY ALL OF THE FOLLOWING BEFORE MARKING ANY TASK COMPLETE:**

- [ ] Did I create/modify an Action? → **MUST** use `application-info`, **MUST** document in `docs/developer/backend/actions/`, **MUST** update manifest
- [ ] Did I add a route? → **MUST** use `list-routes`, **MUST** update `docs/developer/api-reference/routes.md`
- [ ] Did I change the database? → **MUST** use `database-schema`, **MUST** update model docs
- [ ] Is this user-visible? → **MUST** update `docs/user-guide/`
- [ ] Did I update the manifest? → **MUST** update `docs/.manifest.json` with `"documented": true`
- [ ] Did I run `php artisan docs:sync`? → **MUST** sync manifest to ensure accuracy

**If ANY of the above apply and documentation is missing, the task is INCOMPLETE. Do NOT mark as complete.**

## Documentation Generation Process

<!-- Documentation workflow -->
```text
1. **AUTOMATIC TRIGGER**: When creating/modifying Actions, Controllers, Pages, or Routes
2. **MANDATORY**: Use Boost tools to gather context:
   - application-info → Models, packages
   - database-schema → Related tables (if data-related)
   - list-routes → Affected routes
   - search-docs → Laravel best practices (if applicable)
3. **MANDATORY**: Determine documentation scope using decision matrix
4. **MANDATORY**: Generate documentation using appropriate template from docs/.templates/
5. **MANDATORY**: Update manifest at docs/.manifest.json with "documented": true
6. **MANDATORY**: Update relevant index/README files
7. **MANDATORY**: Run `php artisan docs:sync` to verify manifest is accurate
8. **MANDATORY**: Only mark task complete after ALL documentation steps are done
```

## Automated Manifest Sync

The codebase includes an automated manifest sync command:

- **Command**: `php artisan docs:sync`
- **Purpose**: Scans codebase and updates manifest automatically
- **When to run**: After creating documentation, before committing
- **Options**:
  - `--check`: Only check for undocumented items (useful in CI)
  - `--generate`: Auto-generate documentation stubs for undocumented items

**The AI agent MUST run `php artisan docs:sync --check` before marking any task complete to verify all items are documented.**

## AI-Powered Documentation Features

### Available Commands

- `php artisan docs:sync` - Sync manifest and discover relationships
- `php artisan docs:sync --generate` - Generate documentation stubs
- `php artisan docs:sync --generate --ai` - Generate AI prompts for full documentation
- `php artisan docs:review` - Review documentation quality
- `php artisan docs:api` - Generate API documentation

### AI Suggestion Triggers

When creating new code, the AI agent should automatically:

1. **Analyze code complexity** using `DocumentationSuggestionService`:
   - Detect if user guide is needed (user-facing features)
   - Identify if examples are needed (complex parameters, dependencies)
   - Suggest FAQs based on error handling patterns
   - Recommend related documentation to link

2. **Generate suggestions** before creating documentation:
   - Use `DocumentationSuggestionService::suggestDocumentation()` to analyze
   - Review suggestions and include relevant ones in documentation
   - Use `DocumentationSuggestionService::generateSuggestionPrompt()` for AI analysis

3. **Use intelligent template selection**:
   - `DocumentationTemplateSelector` automatically chooses appropriate template
   - Simple templates for basic components
   - Detailed templates for complex components
   - API templates for controllers with many routes

### AI-Powered Generation Workflow

When using `--ai` flag with `docs:sync --generate`:

1. **Extract code context**:
   - PHPDoc/TSDoc comments
   - Method signatures and parameters
   - Dependencies and relationships

2. **Gather Boost MCP context**:
   - `application-info` for models and packages
   - `list-routes` for route information
   - `database-schema` for data relationships

3. **Generate AI prompts**:
   - Prompts saved to `docs/.ai-prompts/`
   - Include all context and relationships
   - Use AI agent to process prompts and generate documentation

4. **Update documentation**:
   - Fill templates with AI-generated content
   - Update manifest automatically
   - Update index files automatically

### Code Change Detection

The system automatically detects when code changes require documentation updates:

- **Pre-commit hook** checks for:
  - Method signature changes
  - Parameter additions/modifications
  - Return type changes
  - New methods added

- **Use `DocumentationChangeDetector`** service to:
  - Detect staged file changes
  - Analyze what changed (signatures, parameters, etc.)
  - Determine if documentation needs update

### Documentation Quality Review

Use `php artisan docs:review` to:

- Compare documentation to actual code
- Verify method signatures match
- Check for outdated information
- Validate cross-references
- Get AI-powered improvement suggestions

### Cross-Referencing

The system automatically discovers and documents relationships:

- **Actions**: Which controllers use them, which models they use, which routes call them
- **Controllers**: Which actions they use, which form requests, which routes, which pages they render
- **Pages**: Which controllers render them, which routes lead to them

All relationships are stored in manifest and automatically included in documentation.

=== .ai/general rules ===

# General Guidelines

- Don't include any superfluous PHP Annotations, except ones that start with `@` for typing variables.

=== .ai/settings rules ===

# Settings & Configuration Guidelines

- Runtime configuration is DB-backed via **spatie/laravel-settings**. Settings classes live in `app/Settings/` and are managed via Filament admin pages.
- Never use `env()` outside of config files. Use `config('key')` — the `SettingsOverlayServiceProvider` writes DB values into config at boot.
- When adding a new setting: create the Settings class, create a settings migration in `database/settings/`, add the mapping to `SettingsOverlayServiceProvider::OVERLAY_MAP`, and create a Filament `SettingsPage`.
- Settings that hold secrets (API keys, passwords) must define `public static function encrypted(): array` returning the encrypted property names.
- 7 groups are org-overridable (Billing, Mail, Stripe, Paddle, LemonSqueezy, Prism, AI). Set `'orgOverridable' => true` in `OVERLAY_MAP` to enable per-org overrides.
- Per-org overrides are stored in the `organization_settings` table and applied by `ApplyOrganizationSettings` middleware after `SetTenantContext`.
- Infrastructure settings (`APP_KEY`, `DB_*`, `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `REDIS_*`, `LOG_CHANNEL`) stay in `.env` — they are needed before the DB is available.
- See `docs/developer/backend/settings.md` for full documentation.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.18
- filament/filament (FILAMENT) - v5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/ai (AI) - v0
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/horizon (HORIZON) - v5
- laravel/mcp (MCP) - v0
- laravel/pennant (PENNANT) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/sanctum (SANCTUM) - v4
- laravel/scout (SCOUT) - v10
- laravel/wayfinder (WAYFINDER) - v0
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/telescope (TELESCOPE) - v5
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- @inertiajs/react (INERTIA_REACT) - v2
- laravel-echo (ECHO) - v2
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v10
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `mcp-development` — Develops MCP servers, tools, resources, and prompts. Activates when creating MCP tools, resources, or prompts; setting up AI integrations; debugging MCP connections; working with routes/ai.php; or when the user mentions MCP, Model Context Protocol, AI tools, AI server, or building tools for AI assistants.
- `pennant-development` — Manages feature flags with Laravel Pennant. Activates when creating, checking, or toggling feature flags; showing or hiding features conditionally; implementing A/B testing; working with @feature directive; or when the user mentions feature flags, feature toggles, Pennant, conditional features, rollouts, or gradually enabling features.
- `wayfinder-development` — Activates whenever referencing backend routes in frontend components. Use when importing from @/actions or @/routes, calling Laravel routes from TypeScript, or working with Wayfinder route functions.
- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `inertia-react-development` — Develops Inertia.js v2 React client-side applications. Activates when creating React pages, forms, or navigation; using &lt;Link&gt;, &lt;Form&gt;, useForm, or router; working with deferred props, prefetching, or polling; or when user mentions React with Inertia, React pages, React forms, or React navigation.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.
- `developing-with-ai-sdk` — Builds AI agents, generates text and chat responses, produces images, synthesizes audio, transcribes speech, generates vector embeddings, reranks documents, and manages files and vector stores using the Laravel AI SDK (laravel/ai). Supports structured output, streaming, tools, conversation memory, middleware, queueing, broadcasting, and provider failover. Use when building, editing, updating, debugging, or testing any AI functionality, including agents, LLMs, chatbots, text generation, image generation, audio, transcription, embeddings, RAG, similarity search, vector stores, prompting, structured output, or any AI provider (OpenAI, Anthropic, Gemini, Cohere, Groq, xAI, ElevenLabs, Jina, OpenRouter).
- `developing-with-fortify` — Laravel Fortify headless authentication backend development. Activate when implementing authentication features including login, registration, password reset, email verification, two-factor authentication (2FA/TOTP), profile updates, headless auth, authentication scaffolding, or auth guards in Laravel applications.
- `developing-with-prism` — Guide for developing with Prism PHP package - a Laravel package for integrating LLMs. Activate or use when working with Prism features including text generation, structured output, embeddings, image generation, audio processing, streaming, tools/function calling, or any LLM provider integration (OpenAI, Anthropic, Gemini, Mistral, Groq, XAI, DeepSeek, OpenRouter, Ollama, VoyageAI, ElevenLabs). Activate for any Prism-related development tasks.
- `database-mail` — Database-backed email templates with martinpetricko/laravel-database-mail. Activates when adding events that should send emails from DB templates; creating or editing mail templates; or when the user mentions database mail, email templates, event-triggered emails, or TriggersDatabaseMail.
- `documentation-automation` — Automates documentation when features are added or modified. Activates when creating Actions, Controllers, Pages, Routes, or Models; when modifying config/fortify.php; or when user mentions docs, documentation, readme.
- `durable-workflow` — Durable Workflow (laravel-workflow) and Waterline. Activates when defining workflows or activities, using WorkflowStub, monitoring workflows at /waterline, or when the user mentions durable workflow, Waterline, long-running workflows, sagas, or workflow orchestration.
- `laravel-data-table` — Server-side DataTables with machour/laravel-data-table (Laravel + Inertia + React, TanStack Table). Activates when building or editing data tables, DataTable classes, table columns/filters/sorting, quick views, exports, or when the user mentions DataTable, data table, server-side table, make:data-table.
- `laravel-excel` — Laravel Excel and Filament Excel exports (maatwebsite/excel, pxlrbt/filament-excel). Activates when adding or editing exports, imports, Filament table exports, DataTable exports, or when the user mentions Laravel Excel, Excel export, import, maatwebsite/excel, or filament-excel.
- `pan-product-analytics` — Product analytics with Pan (panphp/pan). Activates when adding or changing tabs, CTAs, nav links, buttons, or key UI that should be tracked for impressions, hovers, and clicks; or when the user mentions analytics, tracking, Pan, data-pan, or product analytics.
- `taylor-otwell-style` — Code PHP and Laravel applications in the style of Taylor Otwell — the creator of Laravel. Use this skill whenever the user asks to write PHP code, Laravel applications, packages,  APIs, services, or any backend code and wants it to follow Laravel conventions, Taylor  Otwell&#039;s coding philosophy, or &quot;elegant PHP.&quot; Trigger on: Laravel development, PHP package  creation, API design, service classes, Eloquent models, migrations, controllers, middleware, artisan commands, service providers, fluent interfaces, collection pipelines, or any  request mentioning &quot;Laravel-style,&quot; &quot;expressive syntax,&quot; &quot;Taylor Otwell,&quot; or &quot;code like  Laravel.&quot; Also trigger when the user wants to refactor messy PHP into clean, idiomatic  Laravel code. Even if the user just says &quot;write this in PHP&quot; — if you can apply Laravel  patterns to make it better, consult this skill.
- `telescope` — Laravel Telescope debug dashboard (laravel/telescope v5). Activates when configuring or debugging with Telescope; working with watchers, pruning, gates, or when the user mentions Telescope, debug dashboard, requests, queries, jobs, mail monitoring.
- `visibility-sharing` — Visibility and cross-organization sharing with HasVisibility. Activates when working with HasVisibility trait, VisibilityEnum, Shareable, VisibilityScope, shareItem policy, or copy-on-write cloning.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use strict typing at the head of a `.php` file: `declare(strict_types=1);`.
- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== mcp/core rules ===

# Laravel MCP

- Laravel MCP allows you to rapidly build MCP servers for your Laravel applications.
- IMPORTANT: laravel/mcp is very new. Always use the `search-docs` tool for authoritative documentation on writing and testing Laravel MCP servers, tools, resources, and prompts.
- IMPORTANT: Activate `mcp-development` every time you're working with an MCP-related task.

=== pennant/core rules ===

# Laravel Pennant

- This application uses Laravel Pennant for feature flag management, providing a flexible system for controlling feature availability across different organizations and user types.
- IMPORTANT: Always use `search-docs` tool for version-specific Pennant documentation and updated code examples.
- IMPORTANT: Activate `pennant-development` every time you're working with a Pennant or feature-flag-related task.

=== wayfinder/core rules ===

# Laravel Wayfinder

Wayfinder generates TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

- IMPORTANT: Activate `wayfinder-development` skill whenever referencing backend routes in frontend components.
- Invokable Controllers: `import StorePost from '@/actions/.../StorePostController'; StorePost()`.
- Parameter Binding: Detects route keys (`{post:slug}`) — `show({ slug: "my-post" })`.
- Query Merging: `show(1, { mergeQuery: { page: 2, sort: null } })` merges with current URL, `null` removes params.
- Inertia: Use `.form()` with `<Form>` component or `form.submit(store())` with useForm.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== filament/filament rules ===

## Filament

- Filament is used by this application. Follow the existing conventions for how and where it is implemented.
- Filament is a Server-Driven UI (SDUI) framework for Laravel that lets you define user interfaces in PHP using structured configuration objects. Built on Livewire, Alpine.js, and Tailwind CSS.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Always inspect required options before running a command, and always pass `--no-interaction`.

### Patterns

Always use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field visibility" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Actions encapsulate a button with an optional modal form and logic:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data))

</code-snippet>

### Testing

Always authenticate before testing panel functionality. Filament uses Livewire, so use `Livewire::test()` or `livewire()` (available when `pestphp/pest-plugin-livewire` is in `composer.json`):

<code-snippet name="Table test" lang="php">
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1))
    ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Create resource test" lang="php">
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ])
    ->assertNotNotified();

</code-snippet>

<code-snippet name="Calling actions in pages" lang="php">
use Filament\Actions\DeleteAction;
use function Pest\Livewire\livewire;

livewire(EditUser::class, ['record' => $user->id])
    ->callAction(DeleteAction::class)
    ->assertNotified()
    ->assertRedirect();

</code-snippet>

<code-snippet name="Calling actions in tables" lang="php">
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, and `Fieldset` do not span all columns by default. Explicitly set column spans when needed.

=== laravel/ai rules ===

## Laravel AI SDK

- This application uses the Laravel AI SDK (`laravel/ai`) for all AI functionality.
- Activate the `developing-with-ai-sdk` skill when building, editing, updating, debugging, or testing AI agents, text generation, chat, streaming, structured output, tools, image generation, audio, transcription, embeddings, reranking, vector stores, files, conversation memory, or any AI provider integration (OpenAI, Anthropic, Gemini, Cohere, Groq, xAI, ElevenLabs, Jina, OpenRouter).

=== laravel/fortify rules ===

# Laravel Fortify

- Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.
- IMPORTANT: Always use the `search-docs` tool for detailed Laravel Fortify patterns and documentation.
- IMPORTANT: Activate `developing-with-fortify` skill when working with Fortify authentication features.

=== prism-php/prism rules ===

## Prism

- Prism is a Laravel package for integrating Large Language Models (LLMs) into applications with a fluent, expressive and eloquent API.
- IMPORTANT: Activate `developing-with-prism` skill when working with Prism features.

=== filament/blueprint rules ===

## Filament Blueprint

You are writing Filament v5 implementation plans. Plans must be specific enough
that an implementing agent can write code without making decisions.

**Start here**: Read
`/vendor/filament/blueprint/resources/markdown/planning/overview.md` for plan format,
required sections, and what to clarify with the user before planning.

</laravel-boost-guidelines>

---

## Project conventions (survives boost:update)

These apply in addition to the Laravel Boost guidelines above. They are kept **after** the `</laravel-boost-guidelines>` block so `php artisan boost:update` does not remove them.

- **Multi-tenancy:** Organizations own content; use `TenantContext`, `SetTenantContext`, `EnsureTenantContext` (`tenant` middleware), and `BelongsToOrganization` trait. Spatie permissions use `organization_id` as team. Domain/subdomain resolution via `ResolveDomainMiddleware` and `organization_domains`. Single-tenant mode: `MULTI_ORGANIZATION_ENABLED=false` hides org UI. See `config/tenancy.php`, docs/developer/backend/billing-and-tenancy.md, docs/developer/backend/single-tenant-mode.md.
- **Visibility & Sharing:** For global/org/shared data and cross-organization sharing, use the `HasVisibility` trait (do not combine with `BelongsToOrganization` on the same model). Models need `organization_id`, `visibility`; optional `cloned_from` for copy-on-write. Share via `Shareable` (view/edit, optional expiry); authorize with `shareItem` (ShareablePolicy). See docs/developer/backend/visibility-sharing.md, `App\Models\VisibilityDemo`.
- **Org permissions:** JSON-driven org permissions in `database/seeders/data/organization-permissions.json`; run `permission:sync` to create and assign. Use `$user->canInOrganization()`, `@canOrg`, etc. See docs/developer/backend/permissions.md.
- **Billing:** laravelcm/laravel-subscriptions + Stripe + Lemon Squeezy (one-time products); `HasCredits` and `HasBilling` traits on Organization; seat-based billing (`BillingSettings`, `SyncSubscriptionSeatsAction`); billing routes under `tenant` middleware. See `config/billing.php`, `app/Http/Controllers/Billing/`, docs/developer/backend/billing-and-tenancy.md, docs/developer/backend/lemon-squeezy.md.
- **Full-text search:** Use Laravel Scout; driver Typesense (Herd: `SCOUT_DRIVER=typesense`, `TYPESENSE_API_KEY=LARAVEL-HERD`, `TYPESENSE_HOST=localhost`). Add `Searchable` trait and `toSearchableArray()` (id as string, created_at as UNIX timestamp); define collection schema in `config/scout.php` under `typesense.model-settings`. See docs/developer/backend/scout-typesense.md.
- **Third-party APIs:** use Saloon; add connectors and requests under `App\Http\Integrations\{Name}\` (see docs/developer/backend/saloon.md).
- **Server-side DataTables:** machour/laravel-data-table (installed from fork coding-sunshine/laravel-data-table via VCS). One PHP class per model in `App\DataTables\*` (DTO + table config); Inertia + React UI; run `npx shadcn@latest add ./vendor/machour/laravel-data-table/react/public/r/data-table.json` to install React components. To develop the package in place, use a Composer path repository. See docs/developer/backend/data-table.md.
- **Backups:** spatie/laravel-backup (v10) (config/backup.php, docs/developer/backend/backup.md).
- **Userstamps:** wildside/userstamps for created_by/updated_by (docs/developer/backend/userstamps.md).
- **Product analytics (Pan):** panphp/pan tracks impressions, hovers, and clicks via `data-pan="name"` on HTML elements. Use only letters, numbers, dashes, underscores. Add new names to `AppServiceProvider::configurePan()` allowedAnalytics whitelist. View with `php artisan pan` or in Filament at Analytics → Product Analytics (`/admin/analytics/product`). See docs/developer/backend/pan.md. When adding new tabs, CTAs, or key nav/buttons, add `data-pan` and register the name in the whitelist.
- **Database Mail (email templates):** martinpetricko/laravel-database-mail stores email templates in the DB and sends them when events are dispatched. For new events that should send DB-backed emails: implement `TriggersDatabaseMail` and `CanTriggerDatabaseMail`, define `getName()`, `getDescription()`, `getRecipients()`, and optionally `getAttachments()`; register the event in `config/database-mail.php` under `'events'`. Create templates via seeders or Filament plugin. See docs/developer/backend/database-mail.md.
- **Architecture decisions:** record in docs/architecture/ADRs/ (see README there).
- **Theming, branding & page builder:** App theme via `config/theme.php`, `ThemeSettings`, Filament ManageTheme; org branding via `OrganizationSettingsService::getBranding()`, `BrandingController`, `settings/branding.tsx`; custom pages via Puck (`Page` model, `PageController`, `PageViewController`, `puck-config.tsx`, `puck-blocks/`, `PageDataSourceRegistry`). See docs/developer/backend/theming-and-page-builder.md.
- **Durable Workflow & Waterline:** laravel-workflow/laravel-workflow for long-running workflows (sagas, onboarding, AI pipelines); laravel-workflow/waterline UI at `/waterline` (admin only). Workflows run on Laravel queues (Horizon). Gate `viewWaterline` same as Horizon (`access admin panel`). See docs/developer/backend/durable-workflow.md.

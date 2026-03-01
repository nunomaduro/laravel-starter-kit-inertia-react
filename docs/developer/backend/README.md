# Backend Documentation

Backend components, services, and patterns for developers.

**At a glance (for agents):** Public API is versioned at **`/api/v1/`**. **Telescope:** Debug dashboard at `/telescope` (local only; admin panel access); requests, queries, jobs, mail — [telescope.md](./telescope.md). **Database Mail:** martinpetricko/laravel-database-mail stores email templates in DB, linked to events; implement `TriggersDatabaseMail` and `CanTriggerDatabaseMail` on events, register in `config/database-mail.php`; exceptions logged and pruned daily — [database-mail.md](./database-mail.md). (see [API reference](../api-reference/README.md)); list endpoints use **spatie/laravel-query-builder** (filter/sort/include); success/error shape uses **essa/api-tool-kit**. **MCP** server at `POST /mcp/api` (auth:sanctum) exposes tools `users_index`, `users_show` (see [mcp.md](./mcp.md)). **Product analytics:** Pan (panphp/pan) tracks impressions/hovers/clicks via `data-pan` on key UI; whitelist in `AppServiceProvider::configurePan()`; view with `php artisan pan` — [pan.md](./pan.md). **Content & export:** Tags on User (spatie/laravel-tags), profile PDF at `profile.export-pdf`, Filament User export (XLSX/CSV) — see [content-export.md](./content-export.md). Database-backed **settings** live in `App\Settings\*` and are edited in Filament under the **Settings** group (App, Auth, SEO). **Feature flags** in config/feature-flags.php; FeatureHelper for checks; GLOBALLY_DISABLED_MODULES for global disable; shared to Inertia as `features` — [feature-flags.md](./feature-flags.md). **Response cache** (spatie/laravel-responsecache v8) applies to guest GET only (see [response-cache.md](./response-cache.md)). **SEO & monitoring:** Sitemap (`sitemap:generate`, daily), robots.txt route, legal pages (`/legal/terms`, `/legal/privacy`), Sentry, GA4 (spatie/laravel-analytics), Slack webhook (failed-job alerts) — see [seo-and-monitoring.md](./seo-and-monitoring.md). **Backups:** spatie/laravel-backup (v10), scheduled daily (`backup:run` then `backup:clean`) — [backup.md](./backup.md). **Queues:** Laravel Horizon at `/horizon` (admin only; Redis) — [horizon.md](./horizon.md). **Durable Workflows:** laravel-workflow for long-running workflows; Waterline UI at `/waterline` (admin only) — [durable-workflow.md](./durable-workflow.md). **WebSockets:** Laravel Reverb; channels in `routes/channels.php`; Echo in `resources/js/echo.ts` — [reverb.md](./reverb.md). **Categories:** Nested set (kalnoy/nestedset); User has Categorizable trait; Filament Category resource and User categories relation manager — [categorizable.md](./categorizable.md). **Third-party APIs:** use Saloon; connectors in `App\Http\Integrations\*`, example in [saloon.md](./saloon.md). **Server-side DataTables:** machour/laravel-data-table (from fork coding-sunshine); one PHP class per model (DTO + config), Inertia + React UI; DataTable classes in `App\DataTables\*`, shadcn add from vendor — [data-table.md](./data-table.md). **Userstamps:** wildside/userstamps (`created_by`/`updated_by`), see [userstamps.md](./userstamps.md). **Visibility & Sharing:** HasVisibility trait for global/org/shared data and cross-org sharing; copy-on-write — [visibility-sharing.md](./visibility-sharing.md). **ADRs:** architecture decisions in [docs/architecture/ADRs/](../../architecture/ADRs/README.md). **Full-text search:** Laravel Scout + Typesense; `SCOUT_DRIVER=typesense`, `TYPESENSE_*` (Herd: LARAVEL-HERD, localhost:8108); User is searchable — [scout-typesense.md](./scout-typesense.md).

## Contents

- [Actions](./actions/README.md) - Action classes and patterns
- [Activity Log](./activity-log.md) - Spatie and Filament activity logging
- [Backup & Restore](./backup.md) - spatie/laravel-backup (v10), schedule, restore
- [Billing & Multi-Tenancy](./billing-and-tenancy.md) - Seat-based billing, domain/subdomain tenant resolution, ScopesToCurrentTenant
- [Single-Tenant Mode](./single-tenant-mode.md) - Switch to internal (non-SaaS) mode with `MULTI_ORGANIZATION_ENABLED=false`
- [Lemon Squeezy](./lemon-squeezy.md) - One-time products payment gateway (credits checkout)
- [Horizon](./horizon.md) - Queue monitoring and Redis workers (dashboard at `/horizon`)
- [Durable Workflow & Waterline](./durable-workflow.md) - Long-running workflows; Waterline dashboard at `/waterline`
- [Telescope](./telescope.md) - Debug/monitoring dashboard (requests, queries, jobs, mail); local-only; `/telescope`
- [Reverb](./reverb.md) - WebSockets (Laravel Echo + Reverb)
- [Categorizable](./categorizable.md) - Nested set categories; User has Categorizable trait
- [Saloon](./saloon.md) - HTTP client for third-party APIs (connectors, requests)
- [Data Table](./data-table.md) - Server-side DataTables (machour/laravel-data-table from fork); Laravel + Inertia + React
- [Visibility & Sharing](./visibility-sharing.md) - HasVisibility trait; global/org/shared data; copy-on-write
- [Userstamps](./userstamps.md) - created_by / updated_by with wildside/userstamps
- [Controllers](./controllers/README.md) - Controller documentation (web and API v1)
- [Content & export](./content-export.md) - Tags (User), profile PDF, Filament Excel/CSV export
- [Laravel Excel](./laravel-excel.md) - maatwebsite/excel for exports/imports; Filament and DataTable integration
- [Database](./database/README.md) - Database patterns, seeders, and factories
- [Search & Data](./search-and-data.md) - DTOs, Sluggable, Sortable, Model Flags, Schemaless Attributes, Model States, Soft Cascade
- [Filament Admin Panel](./filament.md) - Filament panel at `/admin`
- [Feature Flags](./feature-flags.md) - Laravel Pennant, Filament plugin, Inertia shared props
- [Gamification](./gamification.md) - XP, levels, achievements (cjmellor/level-up); feature-gated
- [Media Library (User avatar)](./media-library.md) - Spatie Media Library and user avatar (conversions, profile)
- [Permissions and RBAC](./permissions.md) - Route-based permissions, permission categories, role hierarchy
- [Prism AI Integration](./prism.md) - AI integration with Prism and OpenRouter
- [Laravel AI SDK](./ai-sdk.md) - Agents, embeddings, images, and when to use vs Prism
- [Laravel AI Memory](./ai-memory.md) - Semantic memory for agents (eznix86/laravel-ai-memory; store/recall, WithMemory)
- [PostgreSQL + pgvector](./pgvector.md) - Vector embeddings with pgvector (optional)
- [Response Cache](./response-cache.md) - Guest GET response caching (exclude auth/admin)
- [Scout + Typesense](./scout-typesense.md) - Full-text search with Laravel Scout and Typesense (Herd)
- [Scramble OpenAPI Docs](./scramble.md) - OpenAPI/Swagger docs at `/docs/api`
- [MCP Server](./mcp.md) - Model Context Protocol server and tools (users_index, users_show); auth via Sanctum
- [Database Mail](./database-mail.md) - Email templates in DB linked to events (martinpetricko/laravel-database-mail); TriggersDatabaseMail, config/database-mail.php
- [Pan (product analytics)](./pan.md) - Privacy-focused product analytics (impressions, hovers, clicks) via `data-pan`; `php artisan pan`
- [SEO & Monitoring](./seo-and-monitoring.md) - Sitemap, robots.txt, legal pages, Sentry, GA4, Slack notifications
- [Settings](./settings.md) - Database-backed settings (app/auth/SEO), Filament Settings pages
- [Theming & Page Builder](./theming-and-page-builder.md) - App theme, org branding, Puck page builder (Page model, PageController, PageViewController, puck-config, blocks)

## Quick Links

- [Actions Documentation](./actions/README.md) - All Action classes
- [Activity Log](./activity-log.md) - User and model activity logging
- [API versioning & list endpoints](../api-reference/README.md) - Public API at `/api/v1/`, filter/sort/include
- [Backup & Restore](./backup.md) - spatie/laravel-backup (v10); schedule, commands, restore
- [Billing & Multi-Tenancy](./billing-and-tenancy.md) - Seat billing, domain tenant resolution, Filament tenant scoping
- [Single-Tenant Mode](./single-tenant-mode.md) - Internal app mode; hides org UI
- [Lemon Squeezy](./lemon-squeezy.md) - One-time products (credits) via Lemon Squeezy
- [Horizon](./horizon.md) - Queue dashboard and Redis workers; `/horizon` (admin only)
- [Durable Workflow & Waterline](./durable-workflow.md) - Workflows and Waterline UI; `/waterline` (admin only)
- [Telescope](./telescope.md) - Debug dashboard; `/telescope` (local; admin panel access)
- [Reverb](./reverb.md) - WebSockets; Echo + `reverb:start`; channels in `routes/channels.php`
- [Categorizable](./categorizable.md) - Nested set categories; User + Filament Category resource
- [Feature Flags](./feature-flags.md) - Pennant + Filament; FeatureHelper; GLOBALLY_DISABLED_MODULES; expose to Inertia via `features` prop
- [Gamification](./gamification.md) - XP, levels, achievements; signup XP, Profile Completed; settings page
- [Response Cache](./response-cache.md) - Guest GET cache; exclude auth/admin
- [Saloon](./saloon.md) - HTTP client for third-party APIs; connectors in `App\Http\Integrations\*`
- [Data Table](./data-table.md) - Server-side DataTables; `App\DataTables\*`; Inertia + React; install from fork (VCS)
- [Laravel Excel](./laravel-excel.md) - maatwebsite/excel; exports/imports; Filament and DataTable integration
- [Scout + Typesense](./scout-typesense.md) - Full-text search; User searchable; Herd env in .env.example
- [Settings](./settings.md) - DB-backed settings; `App\Settings\*`; Filament Settings group
- [Userstamps](./userstamps.md) - created_by/updated_by with wildside/userstamps
- [Seeder System](./database/seeders.md) - Automated seeder system
- [Prism AI Integration](./prism.md) - AI-powered features with Prism (OpenRouter, commands)
- [Laravel AI SDK](./ai-sdk.md) - Agents, embeddings, media; use with Prism as needed
- [Laravel AI Memory](./ai-memory.md) - Semantic memory for agents (store/recall, WithMemory)
- [PostgreSQL + pgvector](./pgvector.md) - Vector embeddings (optional)
- [Scramble OpenAPI Docs](./scramble.md) - API documentation at `/docs/api`
- [Pan (product analytics)](./pan.md) - `data-pan` tracking; `php artisan pan`; whitelist in AppServiceProvider
- [Architecture Decision Records](../../architecture/ADRs/README.md) - ADRs in docs/architecture/ADRs/

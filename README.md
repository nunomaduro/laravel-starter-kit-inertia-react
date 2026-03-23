# Laravel Starter Kit (Inertia + React)

An AI-native SaaS platform framework. Not a boilerplate -- a production-ready foundation with 70+ packages, 13 toggleable modules, and built-in AI infrastructure.

Built with Laravel 13, PHP 8.4, React 19, Inertia v2, Filament v5, and Tailwind v4. Enforces strict types, immutable-first architecture, and 100% type coverage.

---

## Quick Start

```bash
# Prerequisites: PHP 8.4+, Node 20+ or Bun, Composer

composer create-project nunomaduro/laravel-starter-kit-inertia-react my-app
cd my-app
php artisan app:install
composer dev
```

---

## Installation

### Prerequisites

| Requirement | Version | Notes |
|-------------|---------|-------|
| PHP | 8.4+ | With xdebug for coverage |
| Node.js or Bun | 20+ / latest | Frontend build tooling |
| PostgreSQL or SQLite | 15+ / 3.35+ | PostgreSQL recommended for pgvector |
| Redis | 7+ | Optional -- required for Horizon, broadcasting, caching |
| Composer | 2.x | PHP dependency management |

### Step-by-Step

1. **Create the project**

```bash
composer create-project nunomaduro/laravel-starter-kit-inertia-react my-app
cd my-app
```

2. **Install dependencies**

```bash
composer install && npm install   # or bun install
```

3. **Run the interactive installer**

```bash
php artisan app:install
```

The installer runs 24 phases in sequence:

| # | Phase | What it configures |
|---|-------|--------------------|
| 1 | Pre-flight | Environment checks, .env setup |
| 2 | Database | Driver, host, credentials |
| 3 | Migrations | Schema creation |
| 4 | Seeders | Initial data population |
| 5 | Admin | Super-admin account |
| 6 | App | Site name, URL, timezone |
| 7 | Tenancy | Multi-tenant or single-tenant mode |
| 8 | Infrastructure | Cache, session, queue drivers |
| 9 | Mail | SMTP / Mailgun / Postmark |
| 10 | Search | Scout driver, Typesense settings |
| 11 | AI | Provider keys, default model |
| 12 | Social | Google / GitHub OAuth |
| 13 | Storage | Local or S3 filesystem |
| 14 | Broadcasting | Reverb / WebSocket config |
| 15 | SEO | Meta, Open Graph defaults |
| 16 | Monitoring | Sentry DSN, error tracking |
| 17 | Billing | Payment gateway, currency, trials |
| 18 | Integrations | Slack, Postmark, Resend |
| 19 | Theme | Preset, appearance, fonts |
| 20 | Memory | AI memory and embeddings config |
| 21 | Backup | Retention and storage settings |
| 22 | Features | Feature flag toggles |
| 23 | Modules | Enable/disable application modules |
| 24 | Demo | Optional demo data |

Each phase is resumable -- the installer tracks progress and picks up where it left off.

4. **Start development**

```bash
composer dev   # Runs server, queue, logs, and Vite concurrently
```

5. **Verify**

```bash
php artisan env:validate && php artisan app:health
```

---

## Architecture

```
app/
  Actions/          # Business logic (single handle() method per class)
  Console/Commands/ # Artisan commands (app:install, app:configure, module:*)
  Http/Controllers/ # Thin controllers delegating to Actions
  Settings/         # DB-backed runtime settings (spatie/laravel-settings)
  Models/           # Eloquent models with strict defaults

modules/            # Toggleable feature modules (self-contained)
resources/js/       # React 19 + Inertia v2 frontend
  pages/            # Inertia page components
  components/       # Shared UI components

config/             # Framework and module configuration
routes/             # Web, API, console, AI routes
```

**Core subsystems**: Authentication (Fortify), multi-tenancy (organizations + domain resolution), DB-backed settings with config overlay, AI infrastructure (laravel/ai + Prism relay + MCP server).

**Frontend**: Inertia v2 server-driven SPA with React 19, Tailwind v4, and Wayfinder for type-safe route generation.

**Admin**: Filament v5 panel with settings pages, resource management, and product analytics.

---

## Modules

13 self-contained modules under `modules/`. Each can be enabled, disabled, or removed independently.

| Module | Description |
|--------|-------------|
| `blog` | Posts, categories, tags, SEO metadata |
| `changelog` | Product changelog and release notes |
| `help` | Knowledge base with articles and categories |
| `contact` | Contact forms and inquiry management |
| `announcements` | In-app announcements and notifications |
| `gamification` | Points, badges, leaderboards |
| `reports` | Reporting engine and data exports |
| `dashboards` | Customizable dashboard widgets |
| `workflows` | Durable workflow orchestration (Waterline UI) |
| `page-builder` | Visual page builder (Puck) |
| `billing` | Subscriptions, credits, invoices (Stripe / Paddle / LemonSqueezy) |
| `module-hr` | Human resources management |
| `module-crm` | Customer relationship management |

### Module Commands

```bash
php artisan module:list              # Show all modules and their status
php artisan module:enable blog       # Enable a module
php artisan module:disable billing   # Disable a module
php artisan module:remove module-hr  # Remove a module entirely
```

---

## Configuration

After installation, reconfigure any section with:

```bash
php artisan app:configure              # Interactive section picker
php artisan app:configure billing      # Configure a specific section
php artisan app:configure --list       # List all 18 sections
```

**Available sections**: app, mail, ai, billing, search, social, storage, broadcasting, seo, monitoring, tenancy, theme, features, modules, infra, integrations, memory, backup.

---

## Tech Stack

### Backend

| Package | Version | Purpose |
|---------|---------|---------|
| Laravel | 13 | Framework |
| PHP | 8.4 | Runtime |
| Filament | v5 | Admin panel (SDUI) |
| Fortify | v1 | Headless authentication |
| Horizon | v5 | Queue monitoring |
| Sanctum | v4 | API authentication |
| Pennant | v1 | Feature flags |
| Scout | v11 | Full-text search (Typesense) |
| Pulse | v1 | Application monitoring |
| Reverb | v1 | WebSocket broadcasting |
| laravel/ai | v0 | AI SDK (agents, embeddings, tools) |
| Prism | -- | LLM relay and MCP integration |
| Pest | v4 | Testing framework |
| PHPStan / Larastan | v3 | Static analysis (level 9) |
| Rector | v2 | Automated refactoring |
| Pint | v1 | Code formatting |

### Frontend

| Package | Version | Purpose |
|---------|---------|---------|
| React | 19 | UI framework |
| Inertia.js | v2 | Server-driven SPA |
| Tailwind CSS | v4 | Utility-first styling |
| Wayfinder | v0 | Type-safe route generation |
| ESLint | v10 | JavaScript linting |
| Prettier | v3 | Code formatting |

### Design System

Industrial-minimal aesthetic. JetBrains Mono 700 for headings and data. IBM Plex Sans for body text. Muted teal accent (`oklch(0.65 0.14 165)`). Dark-first, no card shadows. See `DESIGN.md` for the full specification.

---

## Development

```bash
composer dev              # Server + queue + logs + Vite (concurrent)
composer lint             # Rector + Pint + Prettier
composer test             # Pest in parallel (compact output)
composer test:full        # Type coverage + unit coverage + lint + static analysis
composer test:types       # PHPStan level 9 + TypeScript checks
composer test:unit        # Pest with 100% code coverage requirement
composer update:requirements  # Bump PHP + NPM dependencies
```

### Key Artisan Commands

```bash
php artisan app:install           # Interactive 24-phase setup
php artisan app:configure         # Reconfigure any section
php artisan module:list           # Module status overview
php artisan seed:environment      # Environment-aware database seeding
php artisan env:validate          # Validate environment variables
php artisan app:health            # Check subsystem health
php artisan make:model:full Post  # Model + factory + seeder + JSON data
php artisan docs:sync             # Sync documentation manifest
```

---

## Testing

520+ tests. 100% type coverage. SQLite schema dump for fast parallel execution (~70s full suite).

```bash
composer test              # Fast parallel run
composer test:full         # Complete suite (coverage, types, lint, static analysis)
composer test:unit         # With 100% code coverage enforcement
composer test:type-coverage # Verify 100% type coverage
```

Browser testing (optional):

```bash
bun add playwright && bunx playwright install
```

---

## Strict Defaults

This project enforces rigorous standards out of the box:

- **Strict models**: `shouldBeStrict()`, `CarbonImmutable` dates, auto-eager-loading, destructive commands prohibited
- **100% type coverage**: Every method, property, and parameter is explicitly typed
- **PHPStan level 9**: Maximum static analysis strictness
- **ECS structured logging**: Available for production observability
- **Pre-commit hooks**: Automated quality checks before every commit

---

## License

**Laravel Starter Kit (Inertia + React)** was created by **[Nuno Maduro](https://x.com/enunomaduro)** under the **[MIT license](https://opensource.org/licenses/MIT)**.

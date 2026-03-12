# Database Seeders

The application uses a comprehensive, automated seeder system that ensures all models have corresponding seeders with proper organization, maintainability, and testing support.

## Overview

The seeder system provides:

- **Category-based organization**: Essential, Development, and Production seeders
- **JSON data support**: Maintainable seed data stored in JSON files
- **Hybrid seeding**: Combine JSON files with Factory states
- **Environment-aware**: Automatically runs appropriate seeders based on environment
- **Dependency management**: Seeders declare dependencies and run in correct order
- **Full automation**: Commands to create, audit, and sync seeders

## Directory Structure

```
database/
├── seeders/
│   ├── DatabaseSeeder.php          # Orchestrator
│   ├── Concerns/
│   │   └── LoadsJsonData.php       # JSON loading trait
│   ├── Essential/                   # Always runs
│   ├── Development/                 # Local/testing only
│   ├── Production/                  # Production only
│   ├── data/                        # JSON seed data
│   │   └── users.json
│   └── manifest.json                # Seeder metadata
└── factories/
    └── UserFactory.php              # With admin() and demo() states
```

## Seeder Categories

### Essential
Seeders that must run in all environments (roles, settings, required lookup data).

**Location**: `database/seeders/Essential/`

**When it runs**: Always, in every environment

**RolesAndPermissionsSeeder** creates core permissions (`bypass-permissions`, `access admin panel`, `view users`, etc.), roles (`super-admin`, `admin`, `user`), assigns `bypass-permissions` to `super-admin`, and assigns admin permissions to `admin`. The `user` role is left with no permissions (authenticated routes like dashboard/settings are in `route_skip_patterns`). When `permission.route_based_enforcement` is true, it runs `permission:sync-routes` so route-named permissions exist after seeding. When `permission.permission_categories_enabled` is true, it uses `PermissionCategoryResolver` and `config/permission_categories.php` to assign permissions by category/wildcard. If `database/seeders/data/organization-permissions.json` exists, it runs `permission:sync` to create org permissions and assign them to org roles. See [Permissions and RBAC](../permissions.md).

### Development
Seeders for local development and testing (fake users, dummy content, test scenarios).

**Location**: `database/seeders/Development/`

**When it runs**: Local and testing environments only

**UsersSeeder** loads fixed users from `database/seeders/data/users.json`, then creates shared organizations (Acme, Beta Co) and attaches users for multi-org and role scenarios. All fixed users use password **`password`**. See [Testing credentials (Development)](#testing-credentials-development) below.

### Seeding and PostgreSQL / Spatie roles

- **`tenancy.seed_in_progress`**: While `DatabaseSeeder` runs, this config is set to `true` so `CreatePersonalOrganizationOnUserCreated` can skip creating personal orgs during bulk user creation (avoids Spatie `assignRole` during seed).
- **Scout during seed**: In local/testing with Development seeders, `DatabaseSeeder` sets `scout.driver` to `collection` so seeding does not require Typesense.
- **Feature flags on local**: After a successful development seed with **zero errors** in the **`local`** environment, `DatabaseSeeder` purges Pennant storage, clears `feature_segments`, and calls `Feature::activateForEveryone(..., true)` for every feature class in `config/feature-flags.php` so **all modules are on** without opening Filament → Manage Features.
- **Role assignment without `assignRole`**: Org and global roles are attached via `model_has_roles` inserts with explicit **`role_id`** (`App\Support\AssignRoleViaDb`) to avoid PostgreSQL errors when team-scoped attach mis-binds role names into bigint columns. Used by `UsersSeeder`, `CreateUser`, `TransferOrganizationOwnershipAction`, `OrganizationMemberController` (member role updates via `syncOrg`), `AppInstallCommand`, and org creation actions. See [AssignRoleViaDb](assign-role-via-db.md).
- **Records created**: `SeedingMetrics::recordCreated()` is invoked once per successful seeder run so the summary line is non-zero; per-model counts can be added in individual seeders if needed.

### Production
Seeders for production-specific data (demo accounts, showcase data).

**Location**: `database/seeders/Production/`

**When it runs**: Production environment only

## Creating Seeders

### Using the Full Model Generator

The recommended way to create a new model with all components:

```bash
php artisan make:model:full Post --category=development --all
```

This creates:
- Model + Migration
- Factory
- Seeder (in specified category)
- JSON data file stub (or auto-generated with AI/Faker)
- Seed spec (canonical description)
- Updates manifest.json
- Analyzes relationships and includes them in seeder

**Options:**
- `--category=development` - Seeder category (essential, development, production)
- `--all` - Generate everything (migration, factory, seeder, controller, policy, requests)
- `--no-ai` - Skip AI generation even if available (use Faker instead)

**Smart Auto-Generation:**
- If JSON file is missing/empty, automatically generates it
- Uses AI if available and configured (OpenRouter, OpenAI, Anthropic)
- Falls back to Faker if AI is unavailable
- Can be disabled with `--no-ai` flag
- Controlled by `config/seeding.php` → `auto_generate_json`

### Manual Seeder Creation

If you need to create a seeder manually:

1. Create the seeder file in the appropriate category directory:
   ```bash
   php artisan make:seeder Development/PostSeeder
   ```

2. Move it to the category directory if needed:
   ```bash
   mv database/seeders/PostSeeder.php database/seeders/Development/
   ```

3. Update the namespace:
   ```php
   namespace Database\Seeders\Development;
   ```

4. Use the `LoadsJsonData` trait:
   ```php
   use Database\Seeders\Concerns\LoadsJsonData;
   
   final class PostSeeder extends Seeder
   {
       use LoadsJsonData;
       // ...
   }
   ```

## Seeder Patterns

### Basic Seeder Structure

```php
<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Post;
use Database\Seeders\Concerns\LoadsJsonData;
use Illuminate\Database\Seeder;

final class PostSeeder extends Seeder
{
    use LoadsJsonData;

    public function run(): void
    {
        $this->seedRelationships();
        $this->seedFromJson();
        $this->seedFromFactory();
    }

    private function seedRelationships(): void
    {
        // Ensure parent models exist
        if (\App\Models\User::query()->count() === 0) {
            \App\Models\User::factory()->count(5)->create();
        }
    }

    private function seedFromJson(): void
    {
        try {
            $data = $this->loadJson('posts.json');
            // Process JSON data
        } catch (\RuntimeException $e) {
            // Skip if JSON doesn't exist
        }
    }

    private function seedFromFactory(): void
    {
        Post::factory()->count(10)->create();
    }
}
```

### JSON Data Format

Store seed data in `database/seeders/data/`:

```json
{
  "posts": [
    {
      "title": "Example Post",
      "content": "Post content",
      "_factory_state": "published"
    }
  ]
}
```

The `_factory_state` key applies a factory state method if it exists.

### Factory States

Use factory states for different scenarios:

```php
// In UserFactory
public function admin(): self
{
    return $this->state(fn (array $attributes): array => [
        'name' => fake()->name().' (Admin)',
        'email' => 'admin@'.fake()->domainName(),
    ]);
}

// In seeder
User::factory()->admin()->count(2)->create();
```

## Running Seeders

### Environment-Aware Seeding

```bash
php artisan seed:environment
```

Automatically runs:
- Essential seeders (always)
- Development seeders (in local/testing)
- Production seeders (in production)

### Specific Category

```bash
php artisan seed:environment --category=development
```

### Specific Seeders

```bash
php artisan seed:environment --only=UsersSeeder,PostsSeeder
```

### Skip Seeders

```bash
php artisan seed:environment --skip=UsersSeeder
```

### Fresh Migration

```bash
php artisan seed:environment --fresh
```

### Production

```bash
php artisan seed:environment --force
```

## Testing credentials (Development)

After `php artisan migrate:fresh --seed` in **local** or **testing**, the following fixed users exist. Use them for manual testing, E2E, and feature verification. All use password **`password`**.

| Email | Global role | Organizations | Use case |
|-------|-------------|----------------|----------|
| **superadmin@example.com** | super-admin | None | System panel (`/admin/system`), setup wizard, impersonation, view-all orgs |
| **test@example.com** | user | 1 (personal, owner) | Regular app user; dashboard, tenant routes |
| **admin-app@example.com** | admin | 1 (personal, owner) | Filament app panel (`/admin`) only; not system panel |
| **owner@example.com** | user | 3 (personal + Acme + Beta Co, owner) | Owns shared orgs used for multi-org scenarios |
| **unverified@example.com** | user | 1 (personal, owner) | Unverified email; test verification flow |
| **onboarding@example.com** | user | 1 (personal, owner) | `onboarding_completed = false`; test onboarding redirect |
| **multi@example.com** | user | 2 (personal owner + Acme admin) | Org switcher; multiple orgs |
| **member@example.com** | user | 1 (Acme only, **member**) | No personal org; org.members.view only (no invite/settings/pages) |
| **mixed@example.com** | user | 3 (personal owner + Acme admin + Beta Co member) | Different org-level roles per org |

**Shared organizations (created by UsersSeeder):**

- **Acme** – owned by `owner@example.com`. Members: `multi@example.com` (admin), `member@example.com` (member), `mixed@example.com` (admin).
- **Beta Co** – owned by `owner@example.com`. Members: `mixed@example.com` (member).

Additional random users are created by the factory (2 global admins, 5 regular users, 2 unverified); their emails are random (e.g. `admin@…`, `user@…`).

## Automation Commands

### List All Seeders

```bash
php artisan seeders:list
```

Options:
- `--category=development` - Filter by category
- `--json` - Output as JSON

### Sync Seeders with Models

```bash
php artisan seeders:sync
```

Options:
- `--update` - Update existing seeders to new patterns
- `--dry-run` - Preview changes without applying

### Audit Models

```bash
php artisan models:audit
```

Shows which models are missing factories or seeders.

Options:
- `--generate` - Auto-generate missing components
- `--category=development` - Default category for generated seeders

## Testing with Seeders

### Auto-Seeding Helper

Use the `seedFor()` helper in tests:

```php
it('can list posts', function () {
    seedFor(Post::class, 5); // Auto-seeds Post + User (author)
    
    $response = $this->get('/posts');
    $response->assertOk();
});
```

### Seed Multiple Models

```php
$results = seedMany([
    'users' => ['class' => User::class, 'count' => 2],
    'posts' => ['class' => Post::class, 'count' => 5],
]);
```

The helper automatically:
- Seeds parent relationships (belongsTo)
- Checks for factories
- Creates required data

## Dependency Management

Seeders can declare dependencies:

```php
final class PostSeeder extends Seeder
{
    /**
     * @var array<string>
     */
    public array $dependencies = ['UsersSeeder'];
    
    // ...
}
```

The orchestrator automatically resolves and runs dependencies in the correct order.

## Manifest File

The `database/seeders/manifest.json` file tracks all seeders with metadata:

```json
{
  "seeders": [
    {
      "name": "UsersSeeder",
      "category": "development",
      "description": "Seeds development users",
      "dependencies": [],
      "data_files": ["users.json"]
    }
  ]
}
```

## Git Pre-Commit Hook

A pre-commit hook runs **Rector**, **Pint**, **model/seeder checks**, and **documentation** checks.

**Source**: `scripts/pre-commit` (versioned). Install into Git:

```bash
cp scripts/pre-commit .git/hooks/pre-commit && chmod +x .git/hooks/pre-commit
```

**Location** (after install): `.git/hooks/pre-commit`

The hook runs in order:
1. **Rector** – Fixes parse errors (e.g. `final` on anonymous classes) before Pint; stages any fixes
2. **Pint** – PHP code style check on dirty files; fails with instructions to run `vendor/bin/pint --dirty --format agent`
3. **New models** – If you staged new model files (in `app/Models/` excluding `Concerns/`, `Scopes/`), requires corresponding seeders and seed specs (blocks commit with fix instructions). Trait/scope helpers (BelongsToOrganization, OrganizationScope, Categorizable, TermsVersion, UserTermsAcceptance) are in `SKIP_NAMES` and exempt
4. **Documentation** – `php artisan docs:sync --check`; fails if items are undocumented

Bypass all checks: `git commit --no-verify`

To fix missing seeders/specs before committing, run `php artisan make:model:full <Model> --category=development` (and optionally `php artisan seeds:spec-sync --model=<Model>`).

## Best Practices

1. **Use `make:model:full`** instead of `make:model` for new models
2. **Run `models:audit`** periodically to check for missing components
3. **Use factory states** for different scenarios (admin, demo, etc.)
4. **Store seed data in JSON** for maintainability
5. **Use `seedFor()` in tests** instead of manual factory calls
6. **Let the pre-commit hook** catch missing seeders automatically
7. **Keep seeders idempotent** - safe to run multiple times
8. **Use `updateOrCreate()`** or `firstOrCreate()` in seeders

## Relationship Handling

The system automatically:
- Analyzes migration files for foreign keys
- Detects belongsTo relationships
- Generates code to seed parent models first
- Handles relationship dependencies

When creating a model with `make:model:full`, relationships are automatically detected and included in the seeder.

## Troubleshooting

### Seeder Not Found

If a seeder isn't being discovered:
1. Check it's in the correct category directory
2. Verify the namespace matches the directory structure
3. Ensure the class extends `Seeder`
4. Check the file isn't in `.gitignore`

### JSON File Not Loading

If JSON data isn't loading:
1. Verify the file exists in `database/seeders/data/`
2. Check JSON syntax is valid
3. Ensure the key matches the model name (plural, snake_case)
4. The seeder will skip silently if JSON doesn't exist (by design)

### Dependencies Not Resolving

If dependencies aren't running in order:
1. Check the `$dependencies` property is correctly declared
2. Verify dependency names match seeder class names (short name)
3. Ensure dependencies are in discoverable categories

## Advanced Features

### Enhanced Relationship Detection

The system now uses **model reflection** to detect ALL relationships:
- Reads actual Eloquent relationship methods
- Extracts foreign keys, pivot tables, and relationship details
- Supports all relationship types (belongsTo, hasMany, belongsToMany, morphTo, etc.)
- More accurate than migration-based detection

**Automatic**: Works automatically when creating models with `make:model:full`

### AI-Powered Seeder Generation

Seeders are now generated using **AI** (with Faker fallback):
- Understands model context, relationships, and fields
- Generates intelligent seeding logic
- Creates idempotent code automatically
- Detects unique fields for updateOrCreate patterns

**Automatic**: Happens automatically when creating models

### Auto-Regeneration on Changes

When you modify models or migrations:
- **MigrationListener** detects relationship changes
- Automatically regenerates seeder code
- Preserves your custom code in protected regions
- Runs silently after migrations

**Configuration:**
```php
// config/seeding.php
'auto_regenerate_seeders' => true, // Auto-regenerate when relationships change
```

### Idempotency by Default

All generated seeders are **idempotent**:
- JSON seeding uses `updateOrCreate()` when unique fields exist
- Safe to run multiple times
- No duplicate records

**Example:**
```php
// Auto-generated code
User::query()->updateOrCreate(
    ['email' => $userData['email']],
    $userData
);
```

### Seed Specs

Seed specs are canonical JSON descriptions of how models should be seeded. They live in `database/seeders/specs/` and are automatically synced with model/migration changes.

**Sync specs:**
```bash
php artisan seeds:spec-sync
```

**Regenerate from specs:**
```bash
php artisan seeds:regenerate
```

### AI-Assisted Generation

Generate realistic seed data using AI (offline, curated):

```bash
php artisan seeds:generate-ai --model=Post --scenario=basic_demo
```

### Test Scenarios

Use named scenarios in tests:

```php
seedScenario('user_with_orders');
```

**Analyze test coverage:**
```bash
php artisan seeds:test-coverage
```

### Real Data Profiling

Profile production/staging to learn patterns:

```bash
php artisan seeds:profile --connection=staging
```

Generate synthetic replicas:

```bash
php artisan seeds:replica --profile=profiles/production.json
```

### Observability

View seeding metrics:

```bash
php artisan seeds:metrics --latest
```

Use strict/lenient modes:

```bash
php artisan seed:environment --strict  # Fail on errors
php artisan seed:environment --lenient  # Continue on warnings
```

### AI Review

Review seeders and specs:

```bash
php artisan seeds:review --model=Post
```

Generate specs from prose:

```bash
php artisan seeds:from-prose "A Project has many Tasks" --model=Project
```

See [Advanced Features](./advanced-features.md) for complete details.

## Related Documentation

- [Factory States](./factories.md) - Factory patterns and states
- [Testing](../testing/README.md) - Testing with seeders
- [Models](../models/README.md) - Model patterns
- [Advanced Features](./advanced-features.md) - Advanced automation features

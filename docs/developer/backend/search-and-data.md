# Search & Data Packages

This document covers the Search & Data packages available in this starter kit, aligned with the boilerplatelivewire feature set. Reference: `compare_features.md` §4.

## Installed Packages

| Package | Priority | Purpose |
|---------|----------|---------|
| **spatie/laravel-data** | Must have | Type-safe DTOs for API/internal contracts |
| **spatie/laravel-sluggable** | Must have | URL-friendly slugs; SEO and route model binding |
| **spatie/eloquent-sortable** (v5) | Good to have | Ordering for lists; Filament `reorderable()` |
| **spatie/laravel-model-flags** | Good to have | Per-instance booleans (featured, pinned) |
| **spatie/laravel-schemaless-attributes** | Good to have | Flexible JSON on models |
| **spatie/laravel-model-states** | Good to have | State machines (draft → published, etc.) |
| **a909m/filament-statefusion** | Good to have | Filament UI for Model States |
| **askedio/laravel-soft-cascade** | Good to have | Cascade soft deletes/restore on relations |
| **propaganistas/laravel-phone** | Good to have | Phone validation, formatting, E164 casting — see [laravel-phone.md](./laravel-phone.md) |

## Spatie Laravel Data (DTOs)

Type-safe Data Transfer Objects for API responses, request validation, and internal contracts.

**Create a Data object:**
```bash
php artisan make:data UserData
```

**Example:**
```php
use App\Models\User;
use Spatie\LaravelData\Data;

final class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
        );
    }
}
```

**Config:** `config/data.php`

## Sluggable

Generate URL-friendly slugs for Eloquent models.

**Usage:**
```php
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
}
```

Add a `slug` column to your migration. Use for SEO and route model binding.

## Eloquent Sortable

Order records (menu items, FAQs, galleries).

**Config:** `config/eloquent-sortable.php`

**Usage:**
```php
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Faq extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];
}
```

Add an `order_column` (or configured name) to your migration. Filament supports `reorderable()` on tables.

## Model Flags

Polymorphic boolean flags without schema changes (featured, pinned, verified).

**Config:** `config/model-flags.php` — uses custom `ModelFlag` model with `model_flags` table to avoid conflicts.

**Migration:** `database/migrations/*_create_model_flags_table.php` (already run).

**Usage:**
```php
use Spatie\ModelFlags\Models\Concerns\HasFlags;

class Post extends Model
{
    use HasFlags;
}

$post->flag('featured');
$post->hasFlag('featured');
$post->unflag('featured');
Post::flagged('featured')->get();
```

**In this app:** `HelpArticle` (featured, pinned), `Announcement`, `Post` (featured). Filament table actions "Feature" / "Unfeature" and "Pin" / "Unpin".

## Schemaless Attributes

Store flexible JSON on models without migrations.

**Usage:**
```php
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

class User extends Model
{
    use SchemalessAttributesTrait;

    public function casts(): array
    {
        return [
            'extra_attributes' => SchemalessAttributes::class,
        ];
    }
}
```

Add `$table->schemalessAttributes('extra_attributes');` in migration.

**In this app:** `Credit`, `Organization`, `Page` each have an `extra_attributes` JSON column for flexible metadata (e.g. `$credit->extra_attributes->campaign_id`).

## Model States

State machines for workflows (draft → published, pending → shipped).

**Usage:**
1. Create abstract state class and concrete states.
2. Add `state` column to migration.
3. Use `HasStates` trait and cast the attribute.
4. Define allowed transitions in the state config.

**Filament StateFusion** provides badges, filters, and transition actions in Filament. The plugin is registered in `AdminPanelProvider`.

**In this app:** `RefundRequest` (RefundRequestStatus), `OrganizationInvitation` (InvitationStatus), `Affiliate`, `AffiliateCommission`, `AffiliatePayout` (each with state classes and custom transitions where needed). Use `$model->status->transitionTo(NewState::class)` instead of updating `status` directly.

## Soft Cascade

Cascade soft deletes and restores to related models. Implemented via **askedio/laravel-soft-cascade**.

**In this app:**
- **User** uses `Askedio\SoftCascade\Traits\SoftCascadeTrait` with `$softCascade = ['ownedOrganizations', 'socialAccounts', 'termsAcceptances', 'notificationPreferences']`. When a user is soft-deleted, their owned organizations, social accounts, terms acceptances, and notification preferences are soft-deleted.
- **Organization** uses `SoftCascadeTrait` with `$softCascade = ['domains', 'invitations']`. When an organization is soft-deleted, its domains and invitations are soft-deleted.

**Usage (for new models):**
1. Add `SoftDeletes` to the model and to any related models that should cascade.
2. Use `SoftCascadeTrait` and define `$softCascade` on the parent:
```php
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class User extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $softCascade = ['posts'];
}
```

## Low Priority (Add When Needed)

- **Full-text search (Scout + Typesense)** — Add when needed; Scout works with Inertia.
- **Flagable (sowailem)** — Social flags (like, follow, bookmark); distinct from Spatie Model Flags.
- **Categorizable & nested set** — Implemented in-app: `App\Models\Concerns\Categorizable`, `App\Models\Category` (kalnoy/nestedset). User has the trait; Filament Category resource and User categories relation manager. See [categorizable.md](./categorizable.md).

## Reference

- Boilerplate docs: `/Users/apple/Code/clients/johnbackhouse/boilerplatelivewire/docs/features/`
- Spatie docs: [spatie.be/docs](https://spatie.be/docs)

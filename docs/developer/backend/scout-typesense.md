# Scout + Typesense (full-text search)

Full-text search is provided by **Laravel Scout** with the **Typesense** driver. Typesense can be run locally (e.g. via [Laravel Herd](https://herd.laravel.com)) or hosted (Typesense Cloud).

## Production checklist

For production search you must:

1. Set `SCOUT_DRIVER=typesense` in your environment (default is `collection`, which is in-memory only).
2. Set `TYPESENSE_API_KEY`, `TYPESENSE_HOST`, and optionally `TYPESENSE_PORT` / `TYPESENSE_PROTOCOL` (use `https` and port `443` for Typesense Cloud).
3. Run `php artisan scout:import "App\Models\User"` (and other searchable models) after deployment to index existing data.

Without these, search in the app and Filament will use the collection driver (in-memory, not persisted). The command palette and API can still offer search by calling Scout; with `collection` driver results are limited to the current request.

## Environment variables

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `SCOUT_DRIVER` | Yes | `collection` | Search driver. Set to `typesense` for Typesense. Use `collection` or `database` for no external server. |
| `TYPESENSE_API_KEY` | Yes (for Typesense) | — | API key. Laravel Herd uses `LARAVEL-HERD`. |
| `TYPESENSE_HOST` | Yes (for Typesense) | `localhost` | Typesense server hostname. |
| `TYPESENSE_PORT` | No | `8108` | Typesense server port. |
| `TYPESENSE_PROTOCOL` | No | `http` | Protocol (`http` or `https`). Use `https` for Typesense Cloud. |
| `TYPESENSE_PATH` | No | — | URL path prefix (used by some hosting setups). |

When `SCOUT_DRIVER` is not set or is `collection`, Scout uses the in-memory collection driver and no Typesense server is required.

Configuration file: `config/scout.php` — driver, Typesense client settings, and per-model collection schemas.

## Searchable models

The following models use the `Laravel\Scout\Searchable` trait and are indexed for search:

### User (`App\Models\User`)

| Field | Type | Notes |
|-------|------|-------|
| `id` | `string` | Cast from int |
| `name` | `string` | |
| `email` | `string` | |
| `created_at` | `int64` | UNIX timestamp |

Search parameters: `query_by: name,email`

### Post (`App\Models\Post`)

| Field | Type | Notes |
|-------|------|-------|
| `id` | `string` | Cast from int |
| `title` | `string` | |
| `excerpt` | `string` | Falls back to empty string |
| `content` | `string` | HTML tags stripped |
| `created_at` | `int64` | UNIX timestamp |
| `updated_at` | `int64` | UNIX timestamp |

### HelpArticle (`App\Models\HelpArticle`)

| Field | Type | Notes |
|-------|------|-------|
| `id` | `string` | Cast from int |
| `title` | `string` | |
| `excerpt` | `string` | Falls back to empty string |
| `content` | `string` | HTML tags stripped |
| `category` | `string` | |
| `created_at` | `int64` | UNIX timestamp |
| `updated_at` | `int64` | UNIX timestamp |

### ChangelogEntry (`App\Models\ChangelogEntry`)

| Field | Type | Notes |
|-------|------|-------|
| `id` | `string` | Cast from int |
| `title` | `string` | |
| `description` | `string` | Falls back to empty string |
| `version` | `string` | |
| `created_at` | `int64` | UNIX timestamp |
| `updated_at` | `int64` | UNIX timestamp |

Each model's `toSearchableArray()` returns `id` as a string and timestamps as UNIX integers — both required by Typesense. Collection schemas are defined in `config/scout.php` under `typesense.model-settings`.

## Adding a new searchable model

1. **Add the trait** to your model:

```php
use Laravel\Scout\Searchable;

class YourModel extends Model
{
    use Searchable;
}
```

2. **Implement `toSearchableArray()`** — return `id` as string and timestamps as UNIX integers:

```php
public function toSearchableArray(): array
{
    return [
        'id' => (string) $this->id,
        'title' => $this->title,
        'created_at' => $this->created_at?->timestamp ?? 0,
    ];
}
```

3. **Define the collection schema** in `config/scout.php` under `typesense.model-settings`:

```php
YourModel::class => [
    'collection-schema' => [
        'fields' => [
            ['name' => 'id', 'type' => 'string'],
            ['name' => 'title', 'type' => 'string'],
            ['name' => 'created_at', 'type' => 'int64'],
        ],
        'default_sorting_field' => 'created_at',
    ],
    'search-parameters' => [
        'query_by' => 'title',
    ],
],
```

4. **Index existing data**:

```bash
php artisan scout:import "App\Models\YourModel"
```

## Indexing existing data

After enabling Typesense or adding a new searchable model, import existing records:

```bash
# Import all records for a model
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\Post"
php artisan scout:import "App\Models\HelpArticle"
php artisan scout:import "App\Models\ChangelogEntry"

# Flush all indexed documents for a model
php artisan scout:flush "App\Models\User"

# Sync index/collection settings
php artisan scout:sync-index-settings
```

## Usage

```php
use App\Models\User;

// Search by query string (searches name, email per config)
$users = User::search('john')->get();

// Paginate
$users = User::search('john')->paginate(15);

// Optional: dynamic search parameters
User::search('john')->options(['query_by' => 'name,email'])->get();
```

## Local development — Laravel Herd

Herd includes a built-in Typesense server. Add these to your `.env`:

```env
SCOUT_DRIVER=typesense
TYPESENSE_API_KEY=LARAVEL-HERD
TYPESENSE_HOST=localhost
TYPESENSE_PORT=8108
```

Start Typesense from the Herd UI (Services tab), then import your data:

```bash
php artisan scout:import "App\Models\User"
```

## Production — Typesense Cloud

For production, use [Typesense Cloud](https://cloud.typesense.org/) or self-host Typesense.

### Typesense Cloud setup

1. Create a cluster at [cloud.typesense.org](https://cloud.typesense.org/).
2. Copy the **API Key**, **Host**, and **Port** from the cluster dashboard.
3. Set your production `.env`:

```env
SCOUT_DRIVER=typesense
TYPESENSE_API_KEY=your-typesense-cloud-api-key
TYPESENSE_HOST=your-cluster-id.a1.typesense.net
TYPESENSE_PORT=443
TYPESENSE_PROTOCOL=https
```

4. Import existing data after deployment:

```bash
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\Post"
php artisan scout:import "App\Models\HelpArticle"
php artisan scout:import "App\Models\ChangelogEntry"
```

### Self-hosted Typesense

Follow the [Typesense installation guide](https://typesense.org/docs/guide/install-typesense.html) and set the same environment variables pointing to your server.

## Scout driver management

The Scout driver can also be changed at runtime via the Filament admin panel at **Settings > Scout** (`/admin/manage-scout`). This writes to the database-backed settings overlay (`ScoutSettings`), which takes precedence over the `.env` value.

## References

- [Laravel Scout](https://laravel.com/docs/scout) — installation, drivers, indexing, searching.
- [Typesense](https://typesense.org/docs/) — schema, search parameters.
- [Typesense Cloud](https://cloud.typesense.org/) — managed hosting.
- Config: `config/scout.php` — `typesense.client-settings`, `typesense.model-settings`.

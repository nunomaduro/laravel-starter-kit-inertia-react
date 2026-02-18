# Response Cache

## Purpose

`spatie/laravel-responsecache` (v8) caches full HTTP responses for **guest** GET requests to improve performance. Authenticated users, admin, and auth-related routes are never cached.

## Configuration

- **Config**: `config/responsecache.php`
- **Profile**: `App\Http\Middleware\CacheProfiles\PublicContentCacheProfile`
  - Caches only **GET** requests
  - Skips when `auth()->check()`
  - Skips paths: `admin/*`, `api/*`, `telescope/*`, `horizon/*`, `pulse/*`, `login`, `register`, `forgot-password`, `reset-password*`, `verify-email*`, `favicon.ico`
  - Caches only successful responses (2xx)
- **Middleware**: `Spatie\ResponseCache\Middlewares\CacheResponse` is appended to the web stack in `bootstrap/app.php`

## Env

| Variable | Description | Default |
|---------|-------------|---------|
| `RESPONSE_CACHE_ENABLED` | Turn response cache on/off | `false` |
| `RESPONSE_CACHE_LIFETIME` | TTL in seconds | `604800` (7 days) |
| `RESPONSE_CACHE_DRIVER` | Cache store name | `file` (from `config/cache.php`) |

## Clearing cache

```bash
php artisan responsecache:clear
```

## Testing

When `APP_ENV=testing`, the cache profile respects `config('responsecache.enabled')`. Disable in tests via `.env.testing` or config override if you need uncached responses.

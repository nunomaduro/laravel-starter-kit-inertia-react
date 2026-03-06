# API Reference

This section documents all routes and endpoints available in the application.

## API versioning

The public API is versioned under **`/api/v1/`**. Use this prefix for all API consumers. The root `/api` returns a short info message; the versioned base is `/api/v1`.

## API response format

Success and error responses follow the **essa/api-tool-kit** format when the request expects JSON (`Accept: application/json` or equivalent):

- **Success (e.g. show, store, batch)**: `{ "status": 200, "message": "...", "data": ... }`
- **Created**: `{ "status": 201, "message": "...", "data": ... }`
- **Deleted**: HTTP 204 No Content (empty body)
- **Validation errors**: `application/problem+json` with an `errors` array (status 422)
- **Other errors**: `application/problem+json` with `errors[{ status, title, detail }]`

List endpoints (index, search) return Laravel API Resource format: `{ "data": [ ... ], "links": ..., "meta": ... }` (paginated).

## Routes

- **Web**: `routes/web.php`
- **API**: `routes/api.php` (prefixed with `/api`; v1 routes under `/api/v1`; documented by [Scramble](../backend/scramble.md) at `/docs/api`)

For the complete list of routes, see [Routes Documentation](./routes.md).

### API routes summary

| Method | URI | Name | Auth |
|--------|-----|------|------|
| GET | `api` | api | — |
| POST | `api/chat` | api.chat | Sanctum |
| GET | `api/chat/memories` | chat.memories | Sanctum |
| GET | `api/conversations` | conversations.index | Sanctum |
| GET | `api/conversations/{id}` | conversations.show | Sanctum |
| PATCH | `api/conversations/{id}` | conversations.update | Sanctum |
| DELETE | `api/conversations/{id}` | conversations.destroy | Sanctum |
| GET | `api/v1` | api.v1.info | — |
| GET | `api/v1/users` | api.v1.users.index | Sanctum, feature:api_access |
| POST | `api/v1/users/batch` | api.v1.users.batch | Sanctum, feature:api_access |
| POST | `api/v1/users/search` | api.v1.users.search | Sanctum, feature:api_access |
| GET | `api/v1/users/{user}` | api.v1.users.show | Sanctum, feature:api_access |
| POST | `api/v1/users` | api.v1.users.store | Sanctum, feature:api_access |
| PUT/PATCH | `api/v1/users/{user}` | api.v1.users.update | Sanctum, feature:api_access |
| DELETE | `api/v1/users/{user}` | api.v1.users.destroy | Sanctum, feature:api_access |

All `/api` and `/api/v1/*` routes use the `api` middleware group. V1 routes are throttled at 60 requests per minute per client. Interactive API docs: `/docs/api`.

## Filter, sort, include, and fields (v1 list endpoints)

List endpoints that use **spatie/laravel-query-builder** support:

- **filter**: `filter[name]=value`, `filter[email]=value` (partial match by default)
- **sort**: `sort=name`, `sort=-created_at` (minus for descending)
- **include**: `include=roles` (e.g. for users index)
- **fields**: `fields[users]=id,name,email` — sparse fieldsets; only requested attributes are returned. Allowed user fields: `id`, `name`, `email`, `email_verified_at`, `created_at`, `updated_at`. Use with list and show where applicable.

See [Scramble](../backend/scramble.md) and the controller docblocks for allowed parameters per endpoint.

> **Note**: Route documentation is regenerated with `php artisan docs:api` when routes are added or modified.

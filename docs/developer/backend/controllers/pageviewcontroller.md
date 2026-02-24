# PageViewController

## Purpose

Renders a single published custom page by slug. Resolves data for data-aware Puck blocks (e.g. members, invoices) and injects resolved data into each block's props. Used for the public/tenant view at `p/{slug}`.

## Location

`app/Http/Controllers/PageViewController.php`

## Methods

| Method | HTTP Method | Route     | Purpose                                      |
|--------|-------------|-----------|----------------------------------------------|
| show   | GET         | `p/{slug}`| Load page by slug, resolve block data, render |

## Routes

- `pages.show`: GET `p/{slug}` - View a published page (tenant context required).

## Actions Used

None. Uses `PageDataSourceRegistry` to resolve data for blocks with a `dataSource` prop.

## Validation

None. Slug is required by route; page is loaded via Eloquent and 404 if not found. View is authorized via `PagePolicy::view` (published or user can manage pages).

## Related Components

- **Page**: `pages/show` (uses `page-view-layout`, Puck `<Render>` with same config as editor)
- **Service**: `PageDataSourceRegistry` – resolves members, invoices, etc. with auth
- **Middleware**: Route must run after tenant middleware so `TenantContext::get()` is set

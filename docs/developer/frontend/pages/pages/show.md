# show

## Purpose

Renders a single custom page built with Puck. Uses a minimal public layout (no app sidebar) with optional org branding. Data-aware blocks receive resolved data injected by the backend.

## Location

`resources/js/pages/pages/show.tsx`

## Route Information

- **URL**: `/p/{slug}`
- **Route Name**: `pages.show`
- **HTTP Method**: GET
- **Middleware**: `tenant` (and auth if view is tenant-only)

## Props (from Controller)

| Prop | Type   | Description                                                                 |
|------|--------|-----------------------------------------------------------------------------|
| page | object | `{ id, name, slug, puck_json }` — `puck_json` has `content` with resolved `data` for data-aware blocks |

## User Flow

1. User or guest visits `/p/{slug}` (with tenant context).
2. Page loads; PageViewLayout shows org branding (logo, name) and main content.
3. Puck `<Render>` renders the same components as the editor using `puck_json`. Data-aware blocks display server-resolved data (e.g. members, invoices).

## Related Components

- **Controller**: `PageViewController@show`
- **Layout**: `page-view-layout.tsx` (minimal; org branding only)
- **Config**: `resources/js/lib/puck-config.tsx` (must match editor config)

---
name: pan-product-analytics
description: "Product analytics with Pan (panphp/pan). Activates when adding or changing tabs, CTAs, nav links, buttons, or key UI that should be tracked for impressions, hovers, and clicks; or when the user mentions analytics, tracking, Pan, data-pan, or product analytics."
license: MIT
metadata:
  author: project
---

# Pan product analytics

## When to apply

Activate when:

- Adding new tabs, toggle groups, or segmented controls
- Adding primary CTAs (login, register, submit, sign up, dashboard)
- Adding nav items, sidebar links, or header links
- Adding help/tooltip triggers or key buttons you want to measure
- User asks about analytics, tracking, or "which button is clicked most"

## What Pan does

- Tracks **impressions** (element viewed), **hovers**, and **clicks**
- Privacy-focused: no IP, user agent, or PII
- Client: add `data-pan="name"` to HTML elements; Pan's injected JS sends events to `/pan/events`
- Server: only analytic **name** and counters stored in `pan_analytics` table

## Rules

1. **Event names:** Only letters, numbers, dashes, and underscores (e.g. `auth-login-button`, `settings-nav-profile`).
2. **Whitelist:** This app uses `PanConfiguration::allowedAnalytics([...])` in `AppServiceProvider::configurePan()`. **Any new `data-pan` value must be added to that array** or it will not be stored.
3. **Naming:** Use kebab-case and a short prefix for context (e.g. `settings-nav-*`, `auth-*`, `welcome-*`, `appearance-tab-*`).

## Implementation

**Frontend (React/Inertia):**

- Add `data-pan="your-name"` to the element (Button, Link, or wrapper that renders to a single DOM node).
- For tabs/toggle items, use a consistent pattern e.g. `appearance-tab-light`, `appearance-tab-dark`.

**Backend:**

- In `App\Providers\AppServiceProvider::configurePan()`, add the new name(s) to the `allowedAnalytics` array.

**Viewing data:**

- `php artisan pan` — table of all analytics
- `php artisan pan --filter=name` — filter by name
- `php artisan pan:flush` — clear all records
- **Filament:** Analytics → Product Analytics (`/admin/analytics/product`) — full table and stats; Product Analytics widget on admin dashboard (admin/super-admin only).

## Documentation

- Full guide: `docs/developer/backend/pan.md`
- Backend at-a-glance: `docs/developer/backend/README.md` (Pan bullet)
- API reference: `docs/developer/api-reference/routes.md` (Pan section, Filament Product Analytics page)

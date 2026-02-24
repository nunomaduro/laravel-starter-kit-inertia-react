# Theming, Branding, and Page Builder

App-wide theming, organization branding, and the Puck-based page builder for custom pages.

## Theming (app-wide)

- **Config:** `config/theme.php` — presets (default, vega, nova), base colors, radii, fonts, appearances.
- **Settings:** `App\Settings\ThemeSettings` (database); edited by superadmin in Filament under **Settings → Manage theme** (`App\Filament\Pages\ManageTheme`).
- **CSS:** `resources/css/themes.css` — `[data-theme="vega"]`, `[data-theme="nova"]`, radius variables; imported in app.css.
- **Inertia/Blade:** `HandleInertiaRequests` shares `theme` from config; `app.blade.php` uses `$theme` for appearance and font `<link>`. Frontend applies via `ThemeFromProps` (`data-theme`, `data-radius`, `data-font`).

## Organization branding

- **Service:** `OrganizationSettingsService::getBranding()` returns logo URL (public disk), theme preset, radius, font, and `allowUserCustomization`.
- **Shared props:** Lazy `branding` in `HandleInertiaRequests`; used by `AppLogo` and theme overrides.
- **UI:** Settings → Branding (`BrandingController`, `resources/js/pages/settings/branding.tsx`); permission `org.settings.manage`. When `allowUserCustomization` is false, user appearance controls are hidden.

## Page builder (Puck)

- **Model:** `App\Models\Page` — `BelongsToOrganization`, slug scoped to org; `puck_json` (root + content); `is_published`; sitemap includes published pages.
- **Routes:** Under tenant: `pages.*` (index, create, store, edit, update, duplicate, destroy) and `p/{slug}` (public view via `PageViewController`).
- **Editor:** `resources/js/pages/pages/edit.tsx` — Puck editor (lazy, `ssr: false`); config in `resources/js/lib/puck-config.tsx`. Blocks in `resources/js/components/puck-blocks/` (Hero, Features, CTA, CardBlock, DataListBlock).
- **View:** `PageViewController@show` loads page by slug, resolves data for data-aware blocks via `PageDataSourceRegistry`, injects into block props, renders `pages/show` with `page-view-layout` and Puck `<Render>`.
- **Data sources:** `App\Services\PageDataSourceRegistry` — register keys (e.g. `members`, `invoices`) with callables; resolution is auth-aware and tenant-scoped.
- **Policy:** `PagePolicy`; permission `org.pages.manage` for create/edit/delete; view allowed for published pages or users who can manage.
- **Revisions:** Each page update creates a `PageRevision` (previous `puck_json`, name, slug, is_published). Preview via `pages.preview` (editors only).
- **SEO:** Optional `meta_description` and `meta_image` on Page; rendered in `pages/show` as `<meta name="description">` and `og:title` / `og:description` / `og:image`.

## PageDataSourceRegistry

Data-aware blocks (e.g. `DataListBlock`) can request server-resolved data by setting a `dataSource` prop. The registry maps keys to callables that are invoked when rendering the page.

- **Register a source:** In a service provider (e.g. `AppServiceProvider`) or where the registry is built, call `PageDataSourceRegistry::register(string $key, callable $callable)`.
- **Callable signature:** `(Organization $organization, ?User $user, array $config): array|Collection`. The `$config` array is the block’s props (e.g. `limit`, `title`). Return an array or Collection of items; the result is merged into the block’s `data` prop.
- **Security:** Resolution runs in the same request as the page view. Always check permissions inside the callable (e.g. `$user->canInOrganization('org.members.view', $organization)`) and return `[]` or limited data when the user is guest or not allowed. Data is tenant-scoped by the controller (organization from `TenantContext`).
- **Defaults:** Built-in keys `members` and `invoices` are registered in `PageDataSourceRegistry::registerDefaults()` and gated by `org.members.view` and `org.billing.view` respectively.

## Accessibility (Puck blocks)

- Use semantic HTML in block components (e.g. `<section>`, `<h1>`–`<h6>`, `<ul>`/`<li>`). The built-in `Heading` and `Text` blocks render plain text (no raw HTML), so they are safe by default.
- If you add a rich-text or embed block that renders user or external content, sanitize HTML and consider CSP. Prefer existing patterns (e.g. `dangerouslySetInnerHTML` only with sanitized content) and document any new blocks in this doc.

## Key files

| Area   | Path |
|--------|------|
| Theme config | `config/theme.php` |
| Theme settings | `app/Settings/ThemeSettings.php`, Filament `ManageTheme` |
| Branding | `OrganizationSettingsService`, `BrandingController`, `settings/branding.tsx` |
| Pages | `app/Models/Page.php`, `PageController`, `PageViewController`, `PagePolicy` |
| Puck config | `resources/js/lib/puck-config.tsx` |
| Blocks | `resources/js/components/puck-blocks/*.tsx` |
| Data | `app/Services/PageDataSourceRegistry.php` |
| Revisions | `app/Models/PageRevision.php`, `page_revisions` table |
| Validation | `app/Rules/ValidPuckJson.php`, `config/pages.puck` |

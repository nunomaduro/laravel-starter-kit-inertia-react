# Laravel Data Table (server-side tables)

Server-side DataTables for **Laravel + Inertia.js + React** are provided by **machour/laravel-data-table** (TanStack Table v8). The package is installed from the project fork: `https://github.com/coding-sunshine/laravel-data-table` (VCS repo in `composer.json`). One PHP class per model defines both the DTO and table configuration; you get sorting, filtering, pagination, quick views, column visibility/ordering, optional exports, and a React UI.

## Installation (already done)

- **Composer**: `machour/laravel-data-table` is required as `dev-main` from the VCS repository (see `composer.json`).
- **React/shadcn**: This repo **already includes** the DataTable React components under `resources/js/components/data-table/` and `resources/js/components/filters/`, including `data-table-column.tsx`, `i18n.ts`, and the full `types.ts`. A **fresh clone** works after `composer setup` (or `composer install`, `bun install`, `bun run build`) — no extra steps.
- **After updating the package**: If you run `composer update machour/laravel-data-table` and want to refresh the app's DataTable React layer, run `bash scripts/sync-data-table-from-vendor.sh`.
- **Reinstalling from the block** (optional): Run `npx shadcn@latest add ./vendor/machour/laravel-data-table/react/public/r/data-table.json --overwrite --yes`, then `scripts/sync-data-table-from-vendor.sh` again.
- **Package fork maintainers:** To make a single `shadcn add` sufficient, add to the block’s `files` in `react/registry.json`: `src/data-table/data-table-column.tsx`, `src/data-table/i18n.ts`, and ensure `src/data-table/types.ts` is the full version; then rebuild `react/public/r/data-table.json`.
- **Optional**: `maatwebsite/excel` for XLSX/CSV export; register export route and use `HasExport` trait on the DataTable class. See [laravel-excel.md](./laravel-excel.md).

## Where things live

- **DataTable classes**: `App\DataTables\*` — extend `Machour\DataTable\AbstractDataTable`, define `tableColumns()`, `tableBaseQuery()`, `tableDefaultSort()`, optional `tableQuickViews()`, `tableFooter()`, and `fromModel()` for the DTO.
- **Pages**: Inertia pages that render a table (e.g. `resources/js/pages/*-table.tsx`) receive `tableData` from the backend and render `<DataTable tableData={tableData} tableName="…" />`.
- **Routes**: Pass `YourDataTable::makeTable()` (or `makeTable($request)`) into `Inertia::render()` for the page that shows the table.

## Quick start

1. **Scaffold** (creates DataTable class + optional React page and route):
   ```bash
   php artisan make:data-table Product
   php artisan make:data-table Product --export --route
   ```
2. **Backend**: In your route, render the Inertia page with `YourDataTable::makeTable()` as the `tableData` prop.
3. **Frontend**: Use the shared `<DataTable>` component with `tableData`, `tableName`, and optional `actions`, `bulkActions`, `renderCell`, `options` (feature flags).

URL state is bookmarkable: `?filter[column]=operator:value&sort=-column&page=1&per_page=25`. Column visibility and order are persisted in localStorage per `tableName`.

## Tables and feature index

| Table | Route | Page | Features demonstrated |
|-------|--------|------|------------------------|
| **Users** | `GET /users` | `users/table.tsx` | Full showcase: sort, filter, pagination, inline edit, toggle, export, import, detail row, quick views, soft deletes, enum/async filters, select-all, bulk/row/header actions, action groups, form-in-action, grouping, rules, **AI** (NLQ, insights, suggest, column summary, enrich, **Thesys Visualize**). |
| **Announcements** | `GET /announcements` | `announcements/table.tsx` | Reorder (position), badges (level, scope), toggle (is_active), enum filters, date range, relational (organization name, creator name), export, quick views. |
| **Posts** | `GET /posts` | `posts/table.tsx` | Badge (draft/published), number (views), relational (author name), dates, quick views, export. |
| **Organizations** | `GET /organizations/list` | `organizations/table.tsx` | Relational (owner name), export, soft deletes (filter trashed). |
| **Categories** | `GET /categories` | `categories/table.tsx` | Cascading filters (type → name), async filter (type), export. |

**AI and Thesys (opt-in):** The Users table has **HasAi** and is registered with `DataTableAiController`. The server passes `dataTableAi: { aiBaseUrl, thesysEnabled }` only when configured: **aiBaseUrl** is set when an AI backend (Laravel AI SDK or Prism) is available and configured; **thesysEnabled** is true only when the app-level Thesys API key is set (`THESYS_API_KEY` in `.env` or `config('services.thesys.api_key')`). If no AI backend or key is configured, the AI panel is hidden; if the Thesys key is missing, the Visualize tab is hidden. Everything is opt-in — no runtime errors when keys are absent. See [Opt-in AI and Thesys](#opt-in-ai-and-thesys) below.

## Users example

- **Backend**: `App\DataTables\UserDataTable` defines DTO (id, name, email, avatar, onboarding_completed, created_at, updated_at), `tableColumns()`, `tableBaseQuery()` (User::query()), and `fromModel(User $model)`.
- **Route**: `GET /users` (name: `users.table`) under auth renders `users/table` with `UserDataTable::inertiaProps($request)` (includes `tableData`, `searchableColumns`, and `dataTableAi` when AI/Thesys are configured).
- **Frontend**: `resources/js/pages/users/table.tsx` renders `<DataTable tableData={tableData} tableName="users" ... />`; when `dataTableAi` is present it passes `aiBaseUrl` and `aiThesys` so the AI panel and Visualize tab are shown only when configured.

## Showcase (Users table – maximum package usage)

The Users table is wired to demonstrate as many package features as possible.

**Backend (UserDataTable):**

- **Traits**: `HasExport`, `HasInlineEdit`, `HasToggle`, `HasSelectAll`, `HasImport`, `HasAuditLog`; detail row and controller registrations for export, inline-edit, toggle, select-all, detail row, import.
- **Columns**: `ColumnBuilder` with types number, text, email, image, boolean (toggleable), date; editable name/email; summary (count on id); groups (Identity, Status, Dates); quick views with `icon` and `columns`.
- **Filters**: `tableAllowedFilters()` with `OperatorFilter` for date and boolean; global search on name, email.
- **Footer / summary**: `tableFooter()` (per-page count), `tableSummary()` (full-dataset count via column `summary`).
- **Rules**: `tableRules()` for row styling by onboarding_completed.
- **Detail row**: `tableDetailRowEnabled()`, `tableDetailRow($model)`; expand fetches `/data-table/detail/users/{id}`.
- **Override**: `resolveToggleUrl()` returns base URL (no `id`) so the frontend can append row id; toggle and inline-edit log to audit via `HasAuditLog`.
- **Import**: `processImport()` for CSV (create/update users, attach to current org).

**Frontend (users/table.tsx):**

- **Props**: `partialReloadKey="tableData"`, `headerActions` (e.g. Add user), `renderDetailRow`, `onInlineEdit`, `onStateChange`; options for density, copyCell, emptyStateIllustration.
- **UI**: Toggle cell (Switch) for boolean columns when `toggleUrl` is present; expand column and detail row when `config.detailRowEnabled` and `renderDetailRow`; summary row when `tableData.summary`; Import button when `tableData.importUrl`; toolbar header actions.

**Config / migrations:**

- `config/data-table.php` published; `data_table_audit_log` and `data_table_saved_views` migrations published and run.

## Full usage examples (Users table)

The Users table is used to demonstrate the full set of features so the app can serve as a reference.

**Backend (UserDataTable):**

- **Group by**: `tableGroupByColumn()` returns `'onboarding_completed'` so the package can group rows (when the package includes groupBy in the payload).
- **Authorize**: `tableAuthorize($action, $request)` returns whether the current user can perform export, import, inline_edit, toggle, etc.
- **Persist state**: `tablePersistState()` returns `true` so filters/sort are persisted (e.g. localStorage) per table.

**Frontend – DataTable component:**

- **Filter chips**: When the URL has active filter params (other than page, per_page, sort, search), a “Clear filters” chip is shown; clicking it clears filter params and keeps sort/search.
- **Density**: Toolbar includes a “Density” dropdown (Compact / Comfortable / Spacious); row padding is applied via `densityRowClass`.
- **Select all matching**: If `tableData.selectAllUrl` is set and total rows > per page, a “Select all X matching” button is shown; it fetches the select-all URL with current query params, expects `{ ids: number[] }`, and sets row selection so bulk actions apply to all matching rows. Requires `getRowId` in `useDataTable` so selection is by id.
- **Copy cell**: When `options.copyCell` is true, each cell (except toggle/image) is wrapped in a hover-only copy button that copies the cell’s string value to the clipboard.
- **Slots**: `slots.toolbar`, `slots.beforeTable`, `slots.afterTable` (and optional `slots.pagination`) are rendered in the layout.
- **Translations**: `translations` overrides default strings (noData, search, export, import, selectAll, selectAllMatching, clearFilters, density, keyboardShortcuts).
- **mobileBreakpoint**: Optional pixel width below which the table can switch to a mobile layout (prop passed through; card layout can be implemented per app).

**Frontend – Row actions:**

- **Confirm dialog**: An action can set `confirm: true` or `confirm: { title, description, confirmLabel, cancelLabel, variant: 'destructive' }`. Clicking the action opens a dialog; on confirm, `onClick(row)` is called.
- **Action groups**: An action can set `group: [ { label, onClick, confirm?, variant? }, ... ]`. The action is rendered as a submenu; sub-actions can have their own `confirm`.

**Frontend – Users page (users/table.tsx):**

- **Example row actions**: “View”, “More” (group: “Send email”, “Log activity”), “Deactivate” (destructive with confirm dialog).
- **Example slots**: `slots.toolbar` with a keyboard-shortcuts button that opens a dialog; `slots.beforeTable` with a short description line.
- **Example translations**: noData, search, clearFilters, density, selectAllMatching overridden.
- **Keyboard shortcuts**: “?” toggles a dialog listing shortcuts (e.g. Ctrl+click row for new tab).
- **mobileBreakpoint**: Set to `768` as an example.

## Additional demos (polling, defer, drawer, soft deletes, bulk confirm)

**Backend:**

- **Soft deletes**: `User` model uses `SoftDeletes`; `users` table has `deleted_at`. `UserDataTable` has `tableSoftDeletesEnabled(): true`, filter `trashed` (values `with` | `only`) via `AllowedFilter::callback`, and quick views "With trashed" / "Only trashed". Status column shows `active` | `pending` | `deleted` (from `fromModel()`).
- **Polling**: `tablePollingInterval(): 30` (seconds). Config merged in route; frontend polls via `router.reload({ only: [partialReloadKey] })` every N seconds.
- **Defer loading**: `tableDeferLoading(): true`; in testing env defer is disabled so tests receive full `tableData`. Route passes `tableData` as `Inertia::defer($makeTableData)` when defer is enabled; users/table shows a skeleton until `tableData` arrives.
- **Detail display**: `tableDetailDisplay(): 'drawer'`; frontend renders detail in a `Sheet` (drawer) instead of inline. Optional `'modal'` uses `Dialog`.

**Frontend:**

- **Detail drawer/modal**: When `config.detailDisplay` is `'drawer'` or `'modal'`, the expand button opens a Sheet or Dialog with `renderDetailRow` content instead of expanding inline.
- **Bulk action confirm**: Bulk actions can set `confirm: { title, description, confirmLabel, cancelLabel, variant }`; the DataTable shows a confirm dialog before calling `onClick(selectedRows)`.

## Opt-in AI and Thesys

DataTable AI (NLQ, insights, suggest, column summary, enrich) and the **Thesys C1 Visualize** tab are **opt-in**:

- **AI panel**: Shown only when an AI backend is available (Laravel AI SDK or Prism) and configured (provider + API key or Ollama). The controller passes `dataTableAi.aiBaseUrl` only in that case; the frontend does not render the AI panel when `aiBaseUrl` is missing.
- **Thesys Visualize tab**: Shown when the app-level Thesys API key is set — via `.env` (`THESYS_API_KEY`) or **Filament → Settings · Integrations → AI** (`AiSettings.thesys_api_key`), which overlays `config('services.thesys.api_key')` at boot. The controller sets `dataTableAi.thesysEnabled` from `config('services.thesys.api_key')`; the frontend passes `aiThesys={true}` only when enabled. The same key is used for any other Thesys C1 features in the app.
- **Installer**: The web installer (AI step) and CLI `app:install` (AI phase) offer an **optional, skippable** field for the Thesys C1 API key. If omitted, Thesys features are disabled; you can add `THESYS_API_KEY` to `.env` later. Express install accepts optional `thesys_api_key` in the request.

If no AI provider/key is set, AI features are disabled; if the Thesys key is not set, the Visualize tab is hidden. No runtime errors when keys are absent.

**Using the Thesys key elsewhere:** The key is stored app-wide. Use `config('services.thesys.api_key')` and `config('services.thesys.model')` (default `c1-nightly`) anywhere you need to call Thesys C1 (e.g. other generative UI features). Set `THESYS_API_KEY` and optionally `THESYS_MODEL` in `.env`.

## Showcase coverage

This app demonstrates: **core** (sort, filter, pagination, URL state, column types), **relational** columns, **editing** (inline edit, toggle, reorder), **export/import**, **row features** (detail row, soft deletes, quick views, cascading/async filters), **actions** (row, bulk, header, groups, confirm), and **AI** (when configured). Not every package feature is showcased (e.g. layout switcher, virtual scroll, saved views DB, PDF export, Kanban).
- **Status badge**: Users table uses `renderCell` for column `status` to render a `Badge` (active = default, pending = secondary, deleted = destructive).
- **Bulk soft-delete**: Route `POST /users/bulk-soft-delete` (name: `users.bulk-soft-delete`); request body `ids[]`; uses `BulkSoftDeleteUsers` action. Users page has a "Delete selected" bulk action with confirm that posts to this route.

## References

- Package README: `vendor/machour/laravel-data-table/README.md`
- Source (fork): [coding-sunshine/laravel-data-table](https://github.com/coding-sunshine/laravel-data-table)
- To develop the package in place, switch to a Composer path repository pointing at a local clone of the fork; see AGENTS.md project conventions.

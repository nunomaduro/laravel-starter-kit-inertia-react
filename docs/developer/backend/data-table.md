# Laravel Data Table (server-side tables)

Server-side DataTables for **Laravel + Inertia.js + React** are provided by **machour/laravel-data-table** (TanStack Table v8). The package is installed from the project fork: `https://github.com/coding-sunshine/laravel-data-table` (VCS repo in `composer.json`). One PHP class per model defines both the DTO and table configuration; you get sorting, filtering, pagination, quick views, column visibility/ordering, optional exports, and a React UI.

## Installation (already done)

- **Composer**: `machour/laravel-data-table` is required as `dev-main` from the VCS repository (see `composer.json`).
- **React/shadcn**: Install the DataTable UI components into the project:
  ```bash
  npx shadcn@latest add ./vendor/machour/laravel-data-table/react/public/r/data-table.json
  ```
  This copies the DataTable React components and installs shadcn/ TanStack Table dependencies.
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

## Users example

- **Backend**: `App\DataTables\UserDataTable` defines DTO (id, name, email, avatar, onboarding_completed, created_at, updated_at), `tableColumns()`, `tableBaseQuery()` (User::query()), and `fromModel(User $model)`.
- **Route**: `GET /users` (name: `users.table`) under auth renders `users/table` with `UserDataTable::makeTable($request)` as `tableData`.
- **Frontend**: `resources/js/pages/users/table.tsx` renders `<DataTable tableData={tableData} tableName="users" />` inside `AppSidebarLayout`.

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
- **Status badge**: Users table uses `renderCell` for column `status` to render a `Badge` (active = default, pending = secondary, deleted = destructive).
- **Bulk soft-delete**: Route `POST /users/bulk-soft-delete` (name: `users.bulk-soft-delete`); request body `ids[]`; uses `BulkSoftDeleteUsers` action. Users page has a "Delete selected" bulk action with confirm that posts to this route.

## References

- Package README: `vendor/machour/laravel-data-table/README.md`
- Source (fork): [coding-sunshine/laravel-data-table](https://github.com/coding-sunshine/laravel-data-table)
- To develop the package in place, switch to a Composer path repository pointing at a local clone of the fork; see AGENTS.md project conventions.

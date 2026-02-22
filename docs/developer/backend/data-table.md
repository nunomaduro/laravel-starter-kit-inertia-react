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

## References

- Package README: `vendor/machour/laravel-data-table/README.md`
- Source (fork): [coding-sunshine/laravel-data-table](https://github.com/coding-sunshine/laravel-data-table)
- To develop the package in place, switch to a Composer path repository pointing at a local clone of the fork; see AGENTS.md project conventions.

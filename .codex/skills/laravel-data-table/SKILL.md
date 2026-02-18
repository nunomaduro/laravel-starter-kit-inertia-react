---
name: laravel-data-table
description: "Server-side DataTables with machour/laravel-data-table (Laravel + Inertia + React, TanStack Table). Activates when building or editing data tables, DataTable classes, table columns/filters/sorting, quick views, exports, or when the user mentions DataTable, data table, server-side table, make:data-table."
license: MIT
metadata:
  author: project
---

# Laravel Data Table (server-side tables)

## When to apply

Activate when building or editing server-side data tables, DataTable PHP classes, table columns/filters/sorting/quick views/exports, or when the user mentions DataTable, data table, server-side table, or `make:data-table`.

## Rules

1. **DataTable classes** live in `App\DataTables\*`; extend `Machour\DataTable\AbstractDataTable`. Define `tableColumns()`, `tableBaseQuery()`, `tableDefaultSort()`, and `fromModel()` for the row DTO. Optional: `tableQuickViews()`, `tableFooter()`, `tableAllowedFilters()`, `tableAllowedSorts()`.
2. **Routes:** Pass `YourDataTable::makeTable()` (or `makeTable($request)`) as the `tableData` prop to the Inertia page that renders the table.
3. **Frontend:** Use the `<DataTable>` component (from shadcn add) with `tableData`, `tableName`, and optional `actions`, `bulkActions`, `renderCell`, `options`. Install React components once: `npx shadcn@latest add ./vendor/machour/laravel-data-table/react/public/r/data-table.json`.
4. **Scaffold:** `php artisan make:data-table ModelName` (options: `--export`, `--route`, `--route-file`, `--page-path`). Package is installed from fork: `coding-sunshine/laravel-data-table` (VCS in composer.json). To develop the package in place, use a Composer path repository.

## Documentation

- **docs/developer/backend/data-table.md** — full guide
- **docs/developer/backend/README.md** — Data Table bullet
- **vendor/machour/laravel-data-table/README.md** — package README

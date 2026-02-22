---
name: laravel-excel
description: "Laravel Excel and Filament Excel exports (maatwebsite/excel, pxlrbt/filament-excel). Activates when adding or editing exports, imports, Filament table exports, DataTable exports, or when the user mentions Laravel Excel, Excel export, import, maatwebsite/excel, or filament-excel."
license: MIT
metadata:
  author: project
---

# Laravel Excel & Filament Excel

## When to apply

Activate when:

- Adding or editing Excel/CSV exports or imports
- Configuring Filament table Export actions
- Adding DataTable export support
- User mentions Laravel Excel, Excel export, import, maatwebsite/excel, or filament-excel

## Packages

- **maatwebsite/excel** (Laravel Excel v3.1) — core export/import
- **pxlrbt/filament-excel** — Filament table exports

## Usage in this application

### Filament (pxlrbt/filament-excel)

- All list tables have `ExportAction` (header) and `ExportBulkAction` (toolbar).
- Use `ExcelExport::make()->fromTable()->withFilename('name-'.now()->format('Y-m-d'))` and `->withWriterType(Excel::CSV)` for CSV.
- For large tables (e.g. Users): add `->withChunkSize(500)->queue()`.

### Custom exports

- Create export classes in `app/Exports/` with `php artisan make:export UsersExport --model=User`.
- Use `Excel::download(new Export, 'file.xlsx')` in controllers.

### Custom imports

- Create import classes in `app/Imports/` with `php artisan make:import UsersImport --model=User`.
- Use `Excel::import(new Import, 'file.xlsx')`.

### DataTable

- Optional `HasExport` trait on DataTable classes; register export route.
- See `docs/developer/backend/data-table.md` and `laravel-excel.md`.

## Artisan commands

- `make:export {name}` — export class
- `make:import {name}` — import class

## Documentation

- Full guide: `docs/developer/backend/laravel-excel.md`
- Backend at-a-glance: `docs/developer/backend/README.md` (Laravel Excel bullet)
- Content export: `docs/developer/backend/content-export.md`

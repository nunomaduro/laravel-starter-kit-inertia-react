# Laravel Excel

**maatwebsite/excel** (Laravel Excel v3.1) is a Laravel-flavoured wrapper around PhpSpreadsheet for exporting and importing Excel/CSV files.

## Installation

- **Composer**: `maatwebsite/excel:^3.1` is required directly.
- **Config**: `config/excel.php` — publish with `php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config` if needed.
- **Auto-discovery**: `ExcelServiceProvider` and `Excel` facade are auto-discovered.

## Features

- Export collections to Excel/CSV
- Export queries with automatic chunking for better performance
- Queue exports for better performance
- Export Blade views to Excel
- Import to collections or models
- Read Excel files in chunks; handle imports in batches

## Usage in this application

### Filament (pxlrbt/filament-excel)

Filament table exports use **pxlrbt/filament-excel**, which builds on Laravel Excel:

- **All list tables** have header Export action (XLSX and CSV) and bulk Export action where applicable.
- **Users table**: Exports are queued with `->queue()` and `->withChunkSize(500)` for better performance on large datasets.
- **Example**: `ExcelExport::make()->fromTable()->withFilename('users-'.now()->format('Y-m-d'))->withChunkSize(500)->queue()`.
- See [content-export.md](./content-export.md) for details.

### DataTable (machour/laravel-data-table)

- **Optional**: `maatwebsite/excel` for XLSX/CSV export; register export route and use `HasExport` trait on the DataTable class.
- See [data-table.md](./data-table.md).

### Custom exports

Create export classes in `app/Exports`:

```bash
php artisan make:export UsersExport --model=User
```

Use in controller:

```php
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

return Excel::download(new UsersExport, 'users.xlsx');
```

### Custom imports

Create import classes in `app/Imports`:

```bash
php artisan make:import UsersImport --model=User
```

Use in controller:

```php
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

Excel::import(new UsersImport, 'users.xlsx');
```

## Artisan commands

- `make:export {name}` — export class
- `make:import {name}` — import class

## References

- [Laravel Excel 3.1 docs](https://docs.laravel-excel.com/3.1/getting-started/)
- [Exports](https://docs.laravel-excel.com/3.1/exports/)
- [Imports](https://docs.laravel-excel.com/3.1/imports/)
- Config: `config/excel.php`

# Reports Module

Drag-and-drop report builder with charts, tables, KPIs, and scheduled exports.

## Purpose

Lets users create reports using the Puck page builder with report-specific blocks. Reports can be exported as PDF, HTML, or CSV, and optionally scheduled via cron expressions for automated generation.

## Structure

```
modules/reports/
├── module.json
├── database/
│   └── migrations/
├── routes/
│   └── web.php
└── src/
    ├── ReportsServiceProvider.php
    ├── Actions/
    │   ├── ExportReportAsCsv.php
    │   ├── ExportReportAsHtml.php
    │   └── ExportReportAsPdf.php
    ├── Console/Commands/
    │   └── DispatchScheduledReportsCommand.php
    ├── Enums/OutputFormat.php           # PDF, HTML, CSV
    ├── Features/ReportsFeature.php
    ├── Http/
    │   ├── Controllers/ReportController.php
    │   └── Requests/
    │       ├── StoreReportRequest.php
    │       └── UpdateReportRequest.php
    ├── Jobs/GenerateScheduledReportJob.php
    ├── Models/
    │   ├── Report.php
    │   └── ReportOutput.php
    ├── Rules/CronExpression.php
    └── Services/ReportDataSourceRegistry.php
```

## Key Classes

- **Models**: `Modules\Reports\Models\Report`, `Modules\Reports\Models\ReportOutput`
- **Feature**: `Modules\Reports\Features\ReportsFeature`
- **Provider**: `Modules\Reports\ReportsServiceProvider`
- **Data Sources**: `ReportDataSourceRegistry` — registers query-based data sources

## Frontend Blocks

Report-specific Puck blocks are in `resources/js/components/puck-blocks/reports/`:
- `TableBlock` — data table with sorting and filtering
- `ChartBlock` — bar, line, and pie charts (Recharts)
- `KpiCard` — single metric with trend indicator
- `FilterBlock` — date range pickers and dropdowns
- `SummaryBlock` — text with template variable resolution

## Toggle

Enable/disable via `config/modules.php` or `php artisan module:enable reports` / `module:disable reports`.

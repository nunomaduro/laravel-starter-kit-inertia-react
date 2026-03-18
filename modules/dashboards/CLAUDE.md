# Dashboards Module

Custom drag-and-drop dashboards with live-refreshing widgets and KPI monitoring.

## Purpose

Lets users create custom dashboards using the Puck page builder with dashboard-specific blocks (live charts, KPI grids, activity feeds, maps, embeds). Dashboards support auto-refresh intervals and a default dashboard per organization.

## Structure

```
modules/dashboards/
├── module.json
├── database/
│   └── migrations/
├── routes/
│   └── web.php
└── src/
    ├── DashboardsServiceProvider.php
    ├── Features/DashboardsFeature.php
    ├── Http/
    │   ├── Controllers/DashboardBuilderController.php
    │   └── Requests/
    │       ├── StoreDashboardRequest.php
    │       └── UpdateDashboardRequest.php
    ├── Models/Dashboard.php
    └── Services/DashboardDataSourceRegistry.php
```

## Key Classes

- **Model**: `Modules\Dashboards\Models\Dashboard`
- **Feature**: `Modules\Dashboards\Features\DashboardsFeature`
- **Provider**: `Modules\Dashboards\DashboardsServiceProvider`
- **Data Sources**: `DashboardDataSourceRegistry` — registers live/cached data sources

## Frontend Blocks

Dashboard-specific Puck blocks are in `resources/js/components/puck-blocks/dashboards/`:
- `LiveChartBlock` — auto-refreshing charts
- `KpiGridBlock` — responsive KPI card grid
- `ActivityFeedBlock` — recent activity stream
- `MapBlock` — geographic data visualization
- `WidgetBlock` — embeddable iframe/component container

## Toggle

Enable/disable via `config/modules.php` or `php artisan module:enable dashboards` / `module:disable dashboards`.

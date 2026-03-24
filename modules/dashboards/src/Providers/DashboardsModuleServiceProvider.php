<?php

declare(strict_types=1);

namespace Modules\Dashboards\Providers;

use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Dashboards\Features\DashboardsFeature;
use Modules\Dashboards\Models\Dashboard;
use Modules\Dashboards\Policies\DashboardPolicy;
use Modules\Dashboards\Services\DashboardDataSourceRegistry;

final class DashboardsModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'dashboards',
            version: '1.0.0',
            description: 'Custom drag-and-drop dashboards with live-refreshing widgets and KPI monitoring.',
            models: [Dashboard::class],
            navigation: [
                ['label' => 'Dashboards', 'route' => 'dashboards.index', 'icon' => 'layout-dashboard', 'group' => 'Platform'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return DashboardsFeature::class;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(DashboardDataSourceRegistry::class);
    }

    protected function bootModule(): void
    {
        Gate::policy(Dashboard::class, DashboardPolicy::class);
    }
}

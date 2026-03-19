<?php

declare(strict_types=1);

namespace Modules\Dashboards;

use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Dashboards\Features\DashboardsFeature;
use Modules\Dashboards\Models\Dashboard;
use Modules\Dashboards\Policies\DashboardPolicy;
use Modules\Dashboards\Services\DashboardDataSourceRegistry;

final class DashboardsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'dashboards';
    }

    public function featureKey(): string
    {
        return 'dashboards';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
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

<?php

declare(strict_types=1);

namespace Modules\Reports;

use App\Support\ModuleServiceProvider;
use Modules\Reports\Features\ReportsFeature;
use Modules\Reports\Services\ReportDataSourceRegistry;

final class ReportsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'reports';
    }

    public function featureKey(): string
    {
        return 'reports';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return ReportsFeature::class;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(ReportDataSourceRegistry::class);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Reports;

use App\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Reports\Console\Commands\DispatchScheduledReportsCommand;
use Modules\Reports\Features\ReportsFeature;
use Modules\Reports\Models\Report;
use Modules\Reports\Models\ReportOutput;
use Modules\Reports\Policies\ReportOutputPolicy;
use Modules\Reports\Policies\ReportPolicy;
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

    protected function bootModule(): void
    {
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(ReportOutput::class, ReportOutputPolicy::class);

        $this->commands([DispatchScheduledReportsCommand::class]);

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('reports:dispatch-scheduled')->everyMinute();
        });
    }
}

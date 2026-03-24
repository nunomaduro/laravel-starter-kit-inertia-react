<?php

declare(strict_types=1);

namespace Modules\Reports\Providers;

use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Reports\Console\Commands\DispatchScheduledReportsCommand;
use Modules\Reports\Features\ReportsFeature;
use Modules\Reports\Models\Report;
use Modules\Reports\Models\ReportOutput;
use Modules\Reports\Policies\ReportOutputPolicy;
use Modules\Reports\Policies\ReportPolicy;
use Modules\Reports\Services\ReportDataSourceRegistry;

final class ReportsModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'reports',
            version: '1.0.0',
            description: 'Drag-and-drop report builder with charts, tables, KPIs, and scheduled exports.',
            models: [
                Report::class,
                ReportOutput::class,
            ],
            navigation: [
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'bar-chart-2', 'group' => 'Platform'],
            ],
        );
    }

    protected function featureClass(): ?string
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

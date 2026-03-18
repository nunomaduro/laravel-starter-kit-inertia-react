<?php

declare(strict_types=1);

namespace Modules\Reports\Console\Commands;

use Cron\CronExpression;
use Illuminate\Console\Command;
use Modules\Reports\Jobs\GenerateScheduledReportJob;
use Modules\Reports\Models\Report;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'reports:dispatch-scheduled')]
final class DispatchScheduledReportsCommand extends Command
{
    /** @var string */
    protected $signature = 'reports:dispatch-scheduled';

    /** @var string */
    protected $description = 'Dispatch jobs for reports whose cron schedule is due now';

    public function handle(): int
    {
        $now = now();

        Report::query()
            ->whereNotNull('schedule')
            ->where('schedule', '!=', '')
            ->each(function (Report $report) use ($now): void {
                $cron = new CronExpression($report->schedule ?? '');

                if ($cron->isDue($now->toDateTimeImmutable())) {
                    GenerateScheduledReportJob::dispatch($report->id);
                    $this->components->info("Dispatched job for report [{$report->id}] {$report->name}");
                }
            });

        return self::SUCCESS;
    }
}

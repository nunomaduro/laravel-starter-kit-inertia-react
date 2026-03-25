<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\Concerns\HasEmbeddings;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class RefreshEmbeddingsCommand extends Command
{
    protected $signature = 'embeddings:refresh
        {model : The fully qualified model class name}
        {--chunk=500 : Number of records to process per chunk}';

    protected $description = 'Refresh embeddings for all records of a given model';

    public function handle(): int
    {
        $modelClass = (string) $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Class [{$modelClass}] does not exist.");

            return self::FAILURE;
        }

        if (! in_array(HasEmbeddings::class, class_uses_recursive($modelClass), true)) {
            $this->error("Class [{$modelClass}] does not use the HasEmbeddings trait.");

            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');

        /** @var Model $instance */
        $instance = new $modelClass;

        /** @var Builder<Model> $query */
        $query = $instance->newQuery()->withoutGlobalScopes();

        $total = $query->count();

        if ($total === 0) {
            $this->info('No records found.');

            return self::SUCCESS;
        }

        $this->info("Dispatching embedding jobs for {$total} {$modelClass} records...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById($chunkSize, function (mixed $records) use ($bar): void {
            /** @var iterable<Model> $records */
            foreach ($records as $record) {
                GenerateEmbeddingJob::dispatch($record);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$total} embedding jobs.");

        return self::SUCCESS;
    }
}

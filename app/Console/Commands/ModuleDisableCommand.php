<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\ModuleLoader;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'module:disable')]
final class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable {name : The module name to disable}';

    protected $description = 'Disable a module (does NOT rollback migrations)';

    public function handle(): int
    {
        $name = (string) $this->argument('name');

        $modules = ModuleLoader::all();

        if (! array_key_exists($name, $modules)) {
            $this->error("Module [{$name}] does not exist in config/modules.php.");
            $this->line('Available modules: '.implode(', ', array_keys($modules)));

            return self::FAILURE;
        }

        if ($modules[$name] === false) {
            $this->info("Module [{$name}] is already disabled.");

            return self::SUCCESS;
        }

        $this->warn("Warning: Disabling [{$name}] will NOT rollback its migrations.");
        $this->warn('Any existing data for this module will remain in the database.');

        $modules[$name] = false;
        ModuleLoader::writeConfig($modules);

        $this->info("Module [{$name}] has been disabled.");

        $this->call('config:clear');

        return self::SUCCESS;
    }
}

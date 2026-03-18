<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\ModuleLoader;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'module:enable')]
final class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable {name : The module name to enable}';

    protected $description = 'Enable a module and run its migrations';

    public function handle(): int
    {
        $name = (string) $this->argument('name');

        $modules = ModuleLoader::all();

        if (! array_key_exists($name, $modules)) {
            $this->error("Module [{$name}] does not exist in config/modules.php.");
            $this->line('Available modules: '.implode(', ', array_keys($modules)));

            return self::FAILURE;
        }

        if ($modules[$name] === true) {
            $this->info("Module [{$name}] is already enabled.");

            return self::SUCCESS;
        }

        $modules[$name] = true;
        ModuleLoader::writeConfig($modules);

        $this->info("Module [{$name}] has been enabled.");

        $this->call('config:clear');

        $this->info('Running migrations...');
        $this->call('migrate', ['--force' => true]);

        return self::SUCCESS;
    }
}

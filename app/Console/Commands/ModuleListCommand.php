<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\ModuleLoader;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'module:list')]
final class ModuleListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List all available modules and their status';

    public function handle(): int
    {
        $modules = ModuleLoader::all();

        if ($modules === []) {
            $this->info('No modules configured in config/modules.php.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($modules as $name => $enabled) {
            $manifest = ModuleLoader::readManifest($name);
            $label = $manifest['label'] ?? ucfirst($name);
            $description = $manifest['description'] ?? '—';

            $rows[] = [
                $name,
                $label,
                $enabled ? '<info>enabled</info>' : '<comment>disabled</comment>',
                $description,
            ];
        }

        $this->table(['Name', 'Label', 'Status', 'Description'], $rows);

        return self::SUCCESS;
    }
}

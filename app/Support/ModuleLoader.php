<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Reads config/modules.php and each module's module.json to discover
 * which modules are enabled and return their provider classes.
 */
final class ModuleLoader
{
    /**
     * Return the list of enabled module service provider class names.
     *
     * Reads config('modules') for the enabled/disabled toggle, then reads
     * each enabled module's module.json for the provider class.
     *
     * @return array<int, class-string>
     */
    public static function providers(): array
    {
        /** @var array<string, bool> $modules */
        $modules = config('modules', []);

        $providers = [];

        foreach ($modules as $name => $enabled) {
            if (! $enabled) {
                continue;
            }

            $manifest = self::readManifest($name);
            if ($manifest === null) {
                continue;
            }

            $providerClass = $manifest['provider'] ?? null;
            if ($providerClass === null || ! is_string($providerClass)) {
                Log::warning("Module [{$name}] module.json missing 'provider' key.");

                continue;
            }

            if (! class_exists($providerClass)) {
                Log::warning("Module [{$name}] provider class [{$providerClass}] not found. Run composer dump-autoload.");

                continue;
            }

            $providers[] = $providerClass;
        }

        return $providers;
    }

    /**
     * Read and decode a module's module.json manifest.
     *
     * @return array<string, mixed>|null
     */
    public static function readManifest(string $moduleName): ?array
    {
        $path = base_path("modules/{$moduleName}/module.json");

        if (! file_exists($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($contents, true);

        return is_array($data) ? $data : null;
    }

    /**
     * Get all module names from config (regardless of enabled/disabled).
     *
     * @return array<string, bool>
     */
    public static function all(): array
    {
        /** @var array<string, bool> $modules */
        $modules = config('modules', []);

        return $modules;
    }

    /**
     * Check if a module is enabled.
     */
    public static function isEnabled(string $name): bool
    {
        return (bool) config("modules.{$name}", false);
    }
}

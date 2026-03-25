<?php

declare(strict_types=1);

namespace App\Support;

use App\Ai\Contracts\ModuleAiTool;
use App\Models\Organization;
use App\Modules\Contracts\ProvidesAITools;
use Laravel\Pennant\Feature;
use WeakMap;

/**
 * Singleton registry that collects AI tools from all enabled modules at boot time.
 *
 * Base tools are always available. Module tools are filtered by Pennant feature flags
 * when resolved for a specific organization.
 */
final class ModuleToolRegistry
{
    /** @var array<int, class-string> */
    private array $baseTools = [];

    /** @var array<int, class-string> */
    private array $moduleTools = [];

    private bool $collected = false;

    /** @var WeakMap<Organization, array<int, object>> */
    private WeakMap $cache;

    public function __construct()
    {
        /** @var WeakMap<Organization, array<int, object>> $map */
        $map = new WeakMap;
        $this->cache = $map;
    }

    /**
     * Register a tool that is always available regardless of feature flags.
     *
     * @param  class-string  $toolClass
     */
    public function registerBaseTool(string $toolClass): void
    {
        if (! in_array($toolClass, $this->baseTools, true)) {
            $this->baseTools[] = $toolClass;
        }
    }

    /**
     * Collect AI tools from all loaded module providers implementing ProvidesAITools.
     *
     * Called during boot. Iterates service providers tagged as module AI tool providers.
     */
    public function collect(): void
    {
        if ($this->collected) {
            return;
        }

        $this->collected = true;

        /** @var array<int, ProvidesAITools> $providers */
        $providers = app()->tagged('module.ai_tools');

        foreach ($providers as $provider) {
            foreach ($provider->registerAiTools() as $toolClass) {
                if (! in_array($toolClass, $this->moduleTools, true)) {
                    $this->moduleTools[] = $toolClass;
                }
            }
        }
    }

    /**
     * Get instantiated tools filtered by feature flags for the given organization.
     *
     * Base tools are always included. Module tools are included only if their
     * required feature is active for the organization (via Pennant).
     *
     * @return array<int, object>
     */
    public function getToolsForOrganization(Organization $org): array
    {
        if (isset($this->cache[$org])) {
            return $this->cache[$org];
        }

        $this->collect();

        $tools = [];

        foreach ($this->baseTools as $toolClass) {
            $tools[] = app($toolClass);
        }

        foreach ($this->moduleTools as $toolClass) {
            if (! is_subclass_of($toolClass, ModuleAiTool::class)) {
                $tools[] = app($toolClass);

                continue;
            }

            $requiredFeature = $toolClass::requiredFeature();

            if ($requiredFeature === null || Feature::for($org)->active($requiredFeature)) {
                $tools[] = app($toolClass);
            }
        }

        $this->cache[$org] = $tools;

        return $tools;
    }

    /**
     * Get all registered base tool class names.
     *
     * @return array<int, class-string>
     */
    public function getBaseTools(): array
    {
        return $this->baseTools;
    }

    /**
     * Get all registered module tool class names.
     *
     * @return array<int, class-string>
     */
    public function getModuleTools(): array
    {
        $this->collect();

        return $this->moduleTools;
    }
}

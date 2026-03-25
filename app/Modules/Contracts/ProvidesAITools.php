<?php

declare(strict_types=1);

namespace App\Modules\Contracts;

/**
 * Modules implement this to register AI tools with the ModuleToolRegistry.
 *
 * Tools returned here are collected at boot time and filtered by feature flags
 * when resolved for a specific organization.
 */
interface ProvidesAITools
{
    /**
     * AI tool class names this module provides.
     *
     * @return array<int, class-string>
     */
    public function registerAiTools(): array;
}

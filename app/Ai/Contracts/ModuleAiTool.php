<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

/**
 * Marker interface for AI tools provided by modules.
 *
 * Tools implementing this can declare a required Pennant feature flag.
 * The ModuleToolRegistry uses this to filter tools per-organization.
 */
interface ModuleAiTool
{
    /**
     * The Pennant feature flag required to activate this tool, or null if always available.
     */
    public static function requiredFeature(): ?string;
}

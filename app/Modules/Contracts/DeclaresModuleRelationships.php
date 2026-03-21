<?php

declare(strict_types=1);

namespace App\Modules\Contracts;

use App\Modules\Support\ModuleRelationship;

/**
 * Modules implement this to declare typed relationships to other modules.
 *
 * Cross-module relationships are registered at boot and gracefully skipped
 * if the target module is not installed.
 */
interface DeclaresModuleRelationships
{
    /**
     * @return array<int, ModuleRelationship>
     */
    public function relationships(): array;
}

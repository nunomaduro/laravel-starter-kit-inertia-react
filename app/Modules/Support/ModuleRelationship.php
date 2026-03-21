<?php

declare(strict_types=1);

namespace App\Modules\Support;

/**
 * Describes a cross-module relationship between two models.
 *
 * Source and target use "module::model" notation (e.g., "hr::employee", "crm::contact").
 */
final readonly class ModuleRelationship
{
    public function __construct(
        public string $sourceModel,
        public string $targetModel,
        public string $type,
        public string $foreignKey,
        public ?string $localKey = null,
    ) {}
}

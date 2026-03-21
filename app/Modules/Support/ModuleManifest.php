<?php

declare(strict_types=1);

namespace App\Modules\Support;

/**
 * Describes a module's metadata — name, version, models, pages, and navigation.
 */
final readonly class ModuleManifest
{
    /**
     * @param  array<int, class-string<\Illuminate\Database\Eloquent\Model>>  $models
     * @param  array<string, string>  $pages  Map of route name => Inertia page component
     * @param  array<int, array{label: string, route: string, icon?: string}>  $navigation
     */
    public function __construct(
        public string $name,
        public string $version,
        public string $description,
        public array $models = [],
        public array $pages = [],
        public array $navigation = [],
    ) {}
}

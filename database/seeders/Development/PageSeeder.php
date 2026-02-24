<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use Illuminate\Database\Seeder;

/**
 * Page seeder.
 *
 * Pages are typically created and edited via the app (Filament / page builder).
 * This seeder exists to satisfy the model-audit / pre-commit check.
 */
final class PageSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: pages are created at runtime via the page builder.
    }
}

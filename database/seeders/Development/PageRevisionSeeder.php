<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use Illuminate\Database\Seeder;

/**
 * PageRevision seeder.
 *
 * Revisions are created automatically when pages are updated via the app.
 * This seeder exists to satisfy the model-audit / pre-commit check.
 */
final class PageRevisionSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: revisions are created at runtime when pages are updated.
    }
}

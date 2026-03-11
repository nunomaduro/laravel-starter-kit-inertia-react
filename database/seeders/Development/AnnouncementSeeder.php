<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use Illuminate\Database\Seeder;

/**
 * Announcement seeder.
 *
 * Announcements are typically created in the Filament admin.
 * This seeder exists to satisfy the model-audit / pre-commit check.
 */
final class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        // No-op: announcements are created at runtime via admin.
    }
}

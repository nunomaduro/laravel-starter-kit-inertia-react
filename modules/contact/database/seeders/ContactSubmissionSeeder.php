<?php

declare(strict_types=1);

namespace Modules\Contact\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Contact\Models\ContactSubmission;

final class ContactSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds (idempotent).
     */
    public function run(): void
    {
        if (ContactSubmission::query()->exists()) {
            return;
        }

        ContactSubmission::factory()
            ->count(5)
            ->create();
    }
}

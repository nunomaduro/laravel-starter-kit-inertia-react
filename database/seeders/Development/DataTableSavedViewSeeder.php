<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\DataTableSavedView;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DataTableSavedViewSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder', 'OrganizationSeeder'];

    public function run(): void
    {
        $org = Organization::query()->first();
        $user = User::query()->first();

        if (! $org || ! $user) {
            return;
        }

        // Personal views (no organization_id — private to user)
        DataTableSavedView::factory()
            ->count(3)
            ->forUser($user)
            ->create();

        // Shared views
        DataTableSavedView::factory()
            ->count(2)
            ->shared($org, $user)
            ->create();

        // System views
        DataTableSavedView::factory()
            ->count(2)
            ->system($org, $user)
            ->create();
    }
}

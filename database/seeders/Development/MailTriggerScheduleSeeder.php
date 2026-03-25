<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\MailTriggerSchedule;
use App\Models\Organization;
use Illuminate\Database\Seeder;

final class MailTriggerScheduleSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['OrganizationSeeder'];

    public function run(): void
    {
        $org = Organization::query()->first();

        if (! $org) {
            return;
        }

        MailTriggerSchedule::factory()
            ->count(3)
            ->create(['organization_id' => $org->id]);
    }
}

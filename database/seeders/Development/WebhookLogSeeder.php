<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Billing\WebhookLog;
use App\Models\Organization;
use Illuminate\Database\Seeder;

final class WebhookLogSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(5)->get();
        $count = fake()->numberBetween(8, 12);

        for ($i = 0; $i < $count; $i++) {
            WebhookLog::factory()->create([
                'organization_id' => $organizations->isNotEmpty() && fake()->boolean(75)
                    ? $organizations->random()->id
                    : null,
            ]);
        }
    }
}

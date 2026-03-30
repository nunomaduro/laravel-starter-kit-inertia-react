<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\WebhookEndpoint;
use Illuminate\Database\Seeder;

final class WebhookEndpointSeeder extends Seeder
{
    /**
     * Run the database seeds (idempotent).
     */
    public function run(): void
    {
        if (WebhookEndpoint::query()->exists()) {
            return;
        }

        WebhookEndpoint::factory()->count(3)->create();
        WebhookEndpoint::factory()->inactive()->count(1)->create();
    }
}

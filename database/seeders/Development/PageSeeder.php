<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use App\Models\Page;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;

final class PageSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(5)->get();

        if ($organizations->isEmpty()) {
            return;
        }

        $total = fake()->numberBetween(5, 10);
        $perOrg = (int) ceil($total / $organizations->count());

        foreach ($organizations as $org) {
            TenantContext::set($org);

            for ($i = 0; $i < $perOrg; $i++) {
                Page::factory()
                    ->when(fake()->boolean(40), fn ($f) => $f->published())
                    ->create(['organization_id' => $org->id]);
            }

            TenantContext::forget();
        }
    }
}

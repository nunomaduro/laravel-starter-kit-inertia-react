<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use App\Models\OrganizationDomain;
use Illuminate\Database\Seeder;

final class OrganizationDomainSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(2)->get();

        if ($organizations->isEmpty()) {
            return;
        }

        $target = fake()->numberBetween(2, 4);
        $domainsByOrg = ['acme.test', 'beta.test', 'acme-staging.test', 'beta-dev.test'];

        for ($i = 0; $i < $target; $i++) {
            $org = $organizations->get($i % $organizations->count());
            $domain = $i < count($domainsByOrg) ? $domainsByOrg[$i] : 'org-'.$org->id.'-'.$i.'.test';
            OrganizationDomain::factory()->create([
                'organization_id' => $org->id,
                'domain' => $domain,
                'type' => 'custom',
                'status' => $i === 0 ? 'active' : fake()->randomElement(['active', 'pending_dns']),
                'is_verified' => fake()->boolean(70),
                'is_primary' => $i === 0,
            ]);
        }
    }
}

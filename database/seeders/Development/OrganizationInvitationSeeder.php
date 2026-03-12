<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Database\Seeder;

final class OrganizationInvitationSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(5)->get();
        $users = User::query()->limit(10)->get();

        if ($organizations->isEmpty() || $users->isEmpty()) {
            return;
        }

        $count = fake()->numberBetween(5, 8);
        $expiredCount = min(2, (int) floor($count * 0.25));

        for ($i = 0; $i < $count; $i++) {
            $org = $organizations->random();
            $inviter = $users->random();
            $useExpired = $expiredCount > 0 && fake()->boolean(25);

            OrganizationInvitation::factory()
                ->when($useExpired, fn ($f) => $f->expired())
                ->create([
                    'organization_id' => $org->id,
                    'email' => fake()->unique()->safeEmail(),
                    'role' => fake()->randomElement(['member', 'admin']),
                    'invited_by' => $inviter->id,
                ]);
            if ($useExpired) {
                $expiredCount--;
            }
        }
    }
}

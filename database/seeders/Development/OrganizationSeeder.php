<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

final class OrganizationSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $user = User::query()->first();

        if ($user === null) {
            return;
        }

        $count = fake()->numberBetween(2, 3);

        for ($i = 0; $i < $count; $i++) {
            $org = Organization::factory()->create([
                'owner_id' => $user->id,
            ]);
            $org->addMember($user, 'admin');
        }
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use App\Models\Shareable;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ShareableSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['UsersSeeder'];

    public function run(): void
    {
        $organizations = Organization::query()->limit(5)->get();
        $users = User::query()->limit(10)->get();

        if ($organizations->count() < 2 || $users->isEmpty()) {
            return;
        }

        $count = fake()->numberBetween(5, 8);

        $created = 0;
        $attempts = 0;
        $maxAttempts = $count * 10;
        while ($created < $count && $attempts < $maxAttempts) {
            $attempts++;
            $shareableOrg = $organizations->random();
            $targetOrg = $organizations->where('id', '!=', $shareableOrg->id)->random();
            $sharer = $users->random();
            $targetType = fake()->boolean(70) ? Organization::class : User::class;
            $targetId = $targetType === Organization::class ? $targetOrg->id : $users->random()->id;

            $attrs = [
                'shareable_type' => Organization::class,
                'shareable_id' => $shareableOrg->id,
                'target_type' => $targetType,
                'target_id' => $targetId,
            ];
            if (Shareable::query()->where($attrs)->exists()) {
                continue;
            }

            Shareable::factory()->create(array_merge($attrs, [
                'permission' => fake()->randomElement(['view', 'edit']),
                'shared_by' => $sharer->id,
                'expires_at' => fake()->optional(0.2)->dateTimeBetween('+1 week', '+1 month'),
            ]));
            $created++;
        }
    }
}

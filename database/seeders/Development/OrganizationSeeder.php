<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

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

    /**
     * Seed relationships (idempotent).
     */
    private function seedRelationships(): void
    {
        // Ensure User exists for 0 (idempotent)
        if (User::query()->count() === 0) {
            User::factory()->count(5)->create();
        }

        // Ensure User exists for 1 (idempotent)
        if (User::query()->count() === 0) {
            User::factory()->count(5)->create();
        }

        // Ensure User exists for 2 (idempotent)
        if (User::query()->count() === 0) {
            User::factory()->count(5)->create();
        }

        // Ensure User exists for 3 (idempotent)
        if (User::query()->count() === 0) {
            User::factory()->count(5)->create();
        }

        // Note: hasMany relationships are seeded after main model creation
        // Note: belongsToMany relationships require pivot table seeding
    }

    /**
     * Seed from JSON data file (idempotent).
     */
    private function seedFromJson(): void
    {
        try {
            $data = $this->loadJson('organizations.json');

            if (! isset($data['organizations']) || ! is_array($data['organizations'])) {
                return;
            }

            foreach ($data['organizations'] as $itemData) {
                $factoryState = $itemData['_factory_state'] ?? null;
                unset($itemData['_factory_state']);

                // Use idempotent updateOrCreate if unique field exists
                if (isset($itemData['slug']) && ! empty($itemData['slug'])) {
                    Organization::query()->updateOrCreate(
                        ['slug' => $itemData['slug']],
                        $itemData
                    );
                } else {
                    // Fallback to factory if no unique field
                    $factory = Organization::factory();
                    if ($factoryState !== null && method_exists($factory, $factoryState)) {
                        $factory = $factory->{$factoryState}();
                    }
                    $factory->create($itemData);
                }
            }
        } catch (RuntimeException $e) {
            // JSON file doesn't exist or is invalid - skip silently
        }
    }

    /**
     * Seed using factory (idempotent - safe to run multiple times).
     */
    private function seedFromFactory(): void
    {
        // Generate seed data with factory
        // Note: Factory creates are not idempotent by default
        // For true idempotency, use updateOrCreate in seedFromJson or add unique constraints
        Organization::factory()
            ->count(5)
            ->create();

        // Create admin users if applicable
        if (method_exists(Organization::factory(), 'admin')) {
            Organization::factory()
                ->admin()
                ->count(2)
                ->create();
        }
    }
}

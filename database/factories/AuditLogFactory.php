<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
final class AuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'actor_id' => User::factory(),
            'actor_type' => User::class,
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'restored']),
            'subject_type' => User::class,
            'subject_id' => fake()->randomNumber(5),
            'old_value' => null,
            'new_value' => ['name' => fake()->name()],
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }

    public function withChanges(array $old, array $new): self
    {
        return $this->state(fn (array $attributes): array => [
            'old_value' => $old,
            'new_value' => $new,
        ]);
    }
}

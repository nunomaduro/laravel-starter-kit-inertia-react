<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Shareable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Shareable>
 */
final class ShareableFactory extends Factory
{
    protected $model = Shareable::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shareable_type' => Organization::class,
            'shareable_id' => Organization::factory(),
            'target_type' => Organization::class,
            'target_id' => Organization::factory(),
            'permission' => 'view',
            'shared_by' => User::factory(),
            'expires_at' => null,
        ];
    }

    public function withEditPermission(): self
    {
        return $this->state(fn (array $attributes): array => [
            'permission' => 'edit',
        ]);
    }

    public function forShareable(Model $model): self
    {
        return $this->state(fn (array $attributes): array => [
            'shareable_type' => $model->getMorphClass(),
            'shareable_id' => $model->getKey(),
        ]);
    }

    public function forOrganization(Organization $organization): self
    {
        return $this->state(fn (array $attributes): array => [
            'target_type' => Organization::class,
            'target_id' => $organization->id,
        ]);
    }

    public function forUser(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'target_type' => User::class,
            'target_id' => $user->id,
        ]);
    }

    public function sharedBy(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'shared_by' => $user->id,
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subDay(),
        ]);
    }
}

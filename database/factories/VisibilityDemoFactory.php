<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VisibilityEnum;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\VisibilityDemo>
 */
final class VisibilityDemoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'organization_id' => Organization::factory(),
            'visibility' => fake()->randomElement(VisibilityEnum::cases()),
            'cloned_from' => null,
        ];
    }

    public function global(): self
    {
        return $this->state(fn (array $attributes): array => [
            'visibility' => VisibilityEnum::Global,
            'organization_id' => null,
        ]);
    }

    public function shared(): self
    {
        return $this->state(fn (array $attributes): array => [
            'visibility' => VisibilityEnum::Shared,
        ]);
    }
}

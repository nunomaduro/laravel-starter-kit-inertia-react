<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ModelFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModelFlag>
 */
final class ModelFlagFactory extends Factory
{
    protected $model = ModelFlag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->slug(2),
        ];
    }
}

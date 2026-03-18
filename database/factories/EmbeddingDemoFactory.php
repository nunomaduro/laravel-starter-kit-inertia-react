<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmbeddingDemo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmbeddingDemo>
 */
final class EmbeddingDemoFactory extends Factory
{
    protected $model = EmbeddingDemo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => fake()->sentence(),
        ];
    }
}

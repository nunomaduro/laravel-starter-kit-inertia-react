<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyImage>
 */
final class PropertyImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'path' => fake()->imageUrl(800, 600, 'architecture'),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}

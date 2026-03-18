<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PageRevision>
 */
final class PageRevisionFactory extends Factory
{
    protected $model = PageRevision::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->sentence(3);

        return [
            'page_id' => Page::factory(),
            'puck_json' => ['root' => (object) [], 'content' => []],
            'name' => $name,
            'slug' => Str::slug($name),
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => true]);
    }
}

<?php

declare(strict_types=1);

namespace Modules\PageBuilder\Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\PageBuilder\Models\Page;

/**
 * @extends Factory<Page>
 */
final class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->sentence(3);

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'puck_json' => ['root' => (object) [], 'content' => []],
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => true]);
    }
}

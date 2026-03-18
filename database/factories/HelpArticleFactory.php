<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HelpArticle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<HelpArticle>
 */
final class HelpArticleFactory extends Factory
{
    protected $model = HelpArticle::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $slug = Str::slug($title);

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(4, true),
            'category' => null,
            'views' => 0,
            'helpful_count' => 0,
            'not_helpful_count' => 0,
            'order' => 0,
            'is_published' => false,
            'is_featured' => false,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
        ]);
    }

    public function featured(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
        ]);
    }

    public function withCategory(string $category): self
    {
        return $this->state(fn (array $attributes): array => [
            'category' => $category,
        ]);
    }
}

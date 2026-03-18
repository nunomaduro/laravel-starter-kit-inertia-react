<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
final class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $slug = Str::slug($title);

        return [
            'author_id' => User::factory(),
            'title' => $title,
            'slug' => $slug,
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(4, true),
            'is_published' => false,
            'published_at' => null,
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'views' => 0,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function draft(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function withSeo(): self
    {
        return $this->state(fn (array $attributes): array => [
            'meta_title' => $attributes['title'] ?? fake()->sentence(),
            'meta_description' => fake()->paragraph(),
            'meta_keywords' => implode(', ', fake()->words(5)),
        ]);
    }
}

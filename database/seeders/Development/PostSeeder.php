<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\Concerns\LoadsJsonData;
use Illuminate\Database\Seeder;
use RuntimeException;

final class PostSeeder extends Seeder
{
    use LoadsJsonData;

    public function run(): void
    {
        try {
            $data = $this->loadJson('posts.json');
        } catch (RuntimeException) {
            $this->command?->warn('Blog posts JSON file not found');

            return;
        }

        $posts = $data['posts'] ?? [];

        foreach ($posts as $postData) {
            $author = User::query()->where('email', $postData['author_email'])->first();

            if (! $author) {
                $this->command?->warn('Author not found: '.$postData['author_email']);

                continue;
            }

            if (Post::query()->where('slug', $postData['slug'])->exists()) {
                continue;
            }

            Post::query()->create([
                'author_id' => $author->id,
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'excerpt' => $postData['excerpt'] ?? null,
                'content' => $postData['content'],
                'is_published' => $postData['is_published'] ?? false,
                'published_at' => $postData['published_at'] ?? null,
                'meta_title' => $postData['meta_title'] ?? null,
                'meta_description' => $postData['meta_description'] ?? null,
                'meta_keywords' => $postData['meta_keywords'] ?? null,
                'views' => $postData['views'] ?? 0,
            ]);
        }

        $this->command?->info('Blog posts seeded.');
    }
}

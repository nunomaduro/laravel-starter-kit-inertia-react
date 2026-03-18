<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\HelpArticle;
use Database\Seeders\Concerns\LoadsJsonData;
use Illuminate\Database\Seeder;
use RuntimeException;

final class HelpArticleSeeder extends Seeder
{
    use LoadsJsonData;

    private const int MIN_ARTICLES = 3;

    public function run(): void
    {
        try {
            $data = $this->loadJson('help-articles.json');
        } catch (RuntimeException) {
            $this->command?->warn('Help articles JSON file not found');
            $this->ensureMinimumArticles();

            return;
        }

        $articles = $data['help_articles'] ?? [];

        foreach ($articles as $article) {
            if (HelpArticle::query()->where('slug', $article['slug'])->exists()) {
                continue;
            }

            $model = HelpArticle::query()->create([
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'] ?? null,
                'content' => $article['content'],
                'category' => $article['category'] ?? null,
                'order' => $article['order'] ?? 0,
                'is_published' => $article['is_published'] ?? false,
            ]);
            if (! empty($article['is_featured'])) {
                $model->flag('featured');
            }
        }

        $this->ensureMinimumArticles();
        $this->command?->info('Help articles seeded.');
    }

    private function ensureMinimumArticles(): void
    {
        $current = HelpArticle::query()->count();
        if ($current >= self::MIN_ARTICLES) {
            return;
        }

        HelpArticle::factory()->count(self::MIN_ARTICLES - $current)->create();
    }
}

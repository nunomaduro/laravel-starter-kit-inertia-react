<?php

declare(strict_types=1);

use App\Models\HelpArticle;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        HelpArticle::query()
            ->where('is_featured', true)
            ->each(fn (HelpArticle $article): HelpArticle => $article->flag('featured'));
    }

    public function down(): void
    {
        HelpArticle::query()->each(fn (HelpArticle $article): HelpArticle => $article->unflag('featured'));
    }
};

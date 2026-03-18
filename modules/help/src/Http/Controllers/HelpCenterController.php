<?php

declare(strict_types=1);

namespace Modules\Help\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Help\Models\HelpArticle;

final class HelpCenterController
{
    public function index(): Response
    {
        $featured = HelpArticle::query()
            ->published()
            ->featured()
            ->orderBy('order')
            ->limit(6)
            ->get();

        $byCategory = HelpArticle::query()
            ->published()
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        return Inertia::render('help/index', [
            'featured' => $featured,
            'byCategory' => $byCategory->map(fn ($articles) => $articles->values()->all())->all(),
        ]);
    }

    public function show(HelpArticle $helpArticle): Response
    {
        abort_unless($helpArticle->is_published, 404);

        $helpArticle->increment('views');
        $helpArticle->load('tags');

        $related = HelpArticle::query()
            ->published()
            ->where('id', '!=', $helpArticle->id)
            ->where('category', $helpArticle->category)
            ->orderBy('order')
            ->limit(5)
            ->get();

        return Inertia::render('help/show', [
            'article' => $helpArticle,
            'related' => $related,
        ]);
    }
}

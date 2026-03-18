<?php

declare(strict_types=1);

namespace Modules\Blog\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Blog\Models\Post;

final class BlogController
{
    public function index(): Response
    {
        $posts = Post::query()
            ->published()
            ->with('author')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('blog/index', [
            'posts' => $posts,
        ]);
    }

    public function show(Post $post): Response
    {
        abort_if(! $post->is_published || ($post->published_at && $post->published_at->isFuture()), 404);

        $post->increment('views');

        $post->load('author', 'tags');

        return Inertia::render('blog/show', [
            'post' => $post,
        ]);
    }
}

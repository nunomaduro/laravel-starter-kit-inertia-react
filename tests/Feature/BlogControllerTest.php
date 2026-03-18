<?php

declare(strict_types=1);

use Modules\Blog\Models\Post;

it('renders blog index with published posts', function (): void {
    $post = Post::factory()->published()->create(['title' => 'Test Post']);

    $response = $this->get(route('blog.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('blog/index')
            ->has('posts')
            ->where('posts.data.0.title', 'Test Post')
        );
});

it('renders blog show for published post', function (): void {
    $post = Post::factory()->published()->create([
        'title' => 'My Post',
        'slug' => 'my-post',
        'content' => '<p>Hello world</p>',
    ]);

    $response = $this->get(route('blog.show', ['post' => $post->slug]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('blog/show')
            ->where('post.title', 'My Post')
            ->where('post.slug', 'my-post')
        );

    $post->refresh();
    expect($post->views)->toBe(1);
});

it('returns 404 for unpublished post', function (): void {
    $post = Post::factory()->draft()->create(['slug' => 'draft-post']);

    $response = $this->get(route('blog.show', ['post' => $post->slug]));

    $response->assertNotFound();
});

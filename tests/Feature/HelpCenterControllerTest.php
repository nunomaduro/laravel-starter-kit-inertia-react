<?php

declare(strict_types=1);

use Modules\Help\Models\HelpArticle;

it('renders help index with featured and by category', function (): void {
    HelpArticle::factory()->published()->featured()->create([
        'title' => 'Getting started',
        'category' => 'General',
    ]);
    HelpArticle::factory()->published()->create([
        'title' => 'Billing',
        'category' => 'Billing',
    ]);

    $response = $this->get(route('help.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('help/index')
            ->has('featured')
            ->has('byCategory')
        );
});

it('renders help show for published article', function (): void {
    $article = HelpArticle::factory()->published()->create([
        'title' => 'How to reset password',
        'slug' => 'reset-password',
        'content' => '<p>Follow these steps.</p>',
    ]);

    $response = $this->get(route('help.show', ['helpArticle' => $article->slug]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('help/show')
            ->where('article.title', 'How to reset password')
            ->has('related')
        );

    $article->refresh();
    expect($article->views)->toBe(1);
});

it('returns 404 for unpublished help article', function (): void {
    $article = HelpArticle::factory()->create([
        'slug' => 'draft-article',
        'is_published' => false,
    ]);

    $response = $this->get(route('help.show', ['helpArticle' => $article->slug]));

    $response->assertNotFound();
});

it('rates help article as helpful', function (): void {
    $article = HelpArticle::factory()->published()->create([
        'slug' => 'some-article',
        'helpful_count' => 0,
        'not_helpful_count' => 0,
    ]);

    $response = $this->post(route('help.rate', ['helpArticle' => $article->slug]), [
        'is_helpful' => true,
    ]);

    $response->assertRedirect()
        ->assertSessionHas('status');

    $article->refresh();
    expect($article->helpful_count)->toBe(1)
        ->and($article->not_helpful_count)->toBe(0);
});

it('rates help article as not helpful', function (): void {
    $article = HelpArticle::factory()->published()->create([
        'slug' => 'other-article',
        'helpful_count' => 0,
        'not_helpful_count' => 0,
    ]);

    $response = $this->post(route('help.rate', ['helpArticle' => $article->slug]), [
        'is_helpful' => false,
    ]);

    $response->assertRedirect()
        ->assertSessionHas('status');

    $article->refresh();
    expect($article->helpful_count)->toBe(0)
        ->and($article->not_helpful_count)->toBe(1);
});

<?php

declare(strict_types=1);

use App\Features\BlogFeature;
use App\Features\ChangelogFeature;
use App\Features\HelpFeature;
use App\Models\ChangelogEntry;
use App\Models\HelpArticle;
use App\Models\Organization;
use App\Models\Post;
use App\Models\User;
use App\Services\TenantContext;
use Laravel\Pennant\Feature;

beforeEach(function (): void {
    $this->user = User::factory()->withoutTwoFactor()->create();
    $this->organization = Organization::factory()->create();
    $this->organization->addMember($this->user, 'admin');
    TenantContext::set($this->organization);
});

it('requires authentication', function (): void {
    $this->getJson(route('search', ['q' => 'test']))
        ->assertUnauthorized();
});

it('returns empty results for an empty query', function (): void {
    $this->actingAs($this->user)
        ->getJson(route('search'))
        ->assertOk()
        ->assertJsonStructure(['users', 'posts', 'help_articles', 'changelog_entries'])
        ->assertJson([
            'users' => [],
            'posts' => [],
            'help_articles' => [],
            'changelog_entries' => [],
        ]);
});

it('searches users within the current organization', function (): void {
    $otherOrg = Organization::factory()->create();
    $otherUser = User::factory()->withoutTwoFactor()->create(['name' => 'Other Person']);
    $otherOrg->addMember($otherUser, 'member');

    $this->user->update(['name' => 'Searchable User']);

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Searchable']))
        ->assertOk()
        ->assertJsonCount(1, 'users')
        ->assertJsonPath('users.0.title', 'Searchable User')
        ->assertJsonPath('users.0.type', 'user');
});

it('searches published posts', function (): void {
    Feature::for($this->user)->activate(BlogFeature::class);

    Post::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Unique Post Title',
    ]);

    Post::factory()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Unique Draft Title',
        'is_published' => false,
    ]);

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Unique']))
        ->assertOk()
        ->assertJsonCount(1, 'posts')
        ->assertJsonPath('posts.0.title', 'Unique Post Title')
        ->assertJsonPath('posts.0.type', 'post');
});

it('searches published help articles', function (): void {
    Feature::for($this->user)->activate(HelpFeature::class);

    HelpArticle::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Help Guide Alpha',
    ]);

    HelpArticle::factory()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Help Guide Beta',
        'is_published' => false,
    ]);

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Help Guide']))
        ->assertOk()
        ->assertJsonCount(1, 'help_articles')
        ->assertJsonPath('help_articles.0.title', 'Help Guide Alpha')
        ->assertJsonPath('help_articles.0.type', 'help_article');
});

it('searches published changelog entries', function (): void {
    Feature::for($this->user)->activate(ChangelogFeature::class);

    ChangelogEntry::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Release Notes Special',
        'version' => '2.0.0',
    ]);

    ChangelogEntry::factory()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Release Notes Draft Special',
        'is_published' => false,
    ]);

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Special']))
        ->assertOk()
        ->assertJsonCount(1, 'changelog_entries')
        ->assertJsonPath('changelog_entries.0.title', 'Release Notes Special')
        ->assertJsonPath('changelog_entries.0.subtitle', 'v2.0.0')
        ->assertJsonPath('changelog_entries.0.type', 'changelog_entry');
});

it('filters results by type', function (): void {
    Feature::for($this->user)->activate(BlogFeature::class);

    $this->user->update(['name' => 'Filterable Person']);

    Post::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Filterable Post',
    ]);

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Filterable', 'type' => 'users']))
        ->assertOk()
        ->assertJsonCount(1, 'users')
        ->assertJsonCount(0, 'posts');
});

it('respects tenant scope for posts', function (): void {
    Feature::for($this->user)->activate(BlogFeature::class);

    $otherOrg = Organization::factory()->create();

    Post::factory()->published()->create([
        'organization_id' => $otherOrg->id,
        'title' => 'Cross Tenant Post',
    ]);

    Post::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Own Tenant Post',
    ]);

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Tenant Post']))
        ->assertOk()
        ->assertJsonCount(1, 'posts')
        ->assertJsonPath('posts.0.title', 'Own Tenant Post');
});

it('returns correct result structure', function (): void {
    $this->user->update(['name' => 'Structure Test User']);

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Structure Test']))
        ->assertOk()
        ->assertJsonStructure([
            'users' => [
                '*' => ['id', 'title', 'subtitle', 'url', 'type'],
            ],
            'posts',
            'help_articles',
            'changelog_entries',
        ]);
});

it('limits results to 5 per category', function (): void {
    for ($i = 0; $i < 7; $i++) {
        $member = User::factory()->withoutTwoFactor()->create(['name' => "Bulkuser {$i}"]);
        $this->organization->addMember($member, 'member');
    }

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Bulkuser']))
        ->assertOk()
        ->assertJsonCount(5, 'users');
});

it('excludes posts when blog feature is disabled', function (): void {
    Feature::for($this->user)->deactivate(BlogFeature::class);

    Post::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'title' => 'Hidden Blog Post',
    ]);

    $this->actingAs($this->user)
        ->getJson(route('search', ['q' => 'Hidden']))
        ->assertOk()
        ->assertJsonCount(0, 'posts');
});

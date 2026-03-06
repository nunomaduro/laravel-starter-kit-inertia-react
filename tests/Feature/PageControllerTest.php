<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\Page;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->withoutTwoFactor()->create();
    $this->org = $this->user->defaultOrganization();
    if (! $this->org) {
        $this->org = Organization::factory()->create();
        $this->org->addMember($this->user, 'admin');
        $this->user->organizations()->updateExistingPivot($this->org->id, ['is_default' => true]);
    }

    Artisan::call('permission:sync', ['--silent' => true]);
    $this->session = ['current_organization_id' => $this->org->id];
});

it('requires authentication for pages index', function (): void {
    $response = $this->get(route('pages.index'));

    $response->assertRedirect(route('login'));
});

it('requires tenant context for pages index', function (): void {
    $this->user->organizations()->detach();
    TenantContext::forget();
    $response = $this->actingAs($this->user)->get(route('pages.index'));

    $response->assertRedirect(route('dashboard'));
});

it('shows pages index for user with org.pages.manage', function (): void {
    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->get(route('pages.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('pages/index')
            ->has('pages')
        );
});

it('allows creating a page', function (): void {
    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->post(route('pages.store'), [
            'name' => 'About Us',
            'slug' => 'about-us',
            'puck_json' => ['root' => (object) [], 'content' => []],
        ]);

    $response->assertRedirect();

    expect(Page::query()->where('slug', 'about-us')->where('organization_id', $this->org->id)->exists())->toBeTrue();
});

it('validates puck_json component types', function (): void {
    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->post(route('pages.store'), [
            'name' => 'Test',
            'slug' => 'test',
            'puck_json' => [
                'root' => (object) [],
                'content' => [['type' => 'DisallowedBlock', 'props' => []]],
            ],
        ]);

    $response->assertSessionHasErrors('puck_json');
});

it('allows editing a page', function (): void {
    $page = Page::factory()->for($this->org)->create();

    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->get(route('pages.edit', $page));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('pages/edit')
            ->has('page')
            ->has('puckJson')
        );
});

it('allows updating a page and creates revision', function (): void {
    $page = Page::factory()->for($this->org)->create(['name' => 'Old', 'slug' => 'old']);

    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->put(route('pages.update', $page), [
            'name' => 'Updated',
            'slug' => 'updated',
            'puck_json' => $page->puck_json,
            'is_published' => false,
        ]);

    $response->assertRedirect();

    $page->refresh();
    expect($page->name)->toBe('Updated')
        ->and($page->slug)->toBe('updated')
        ->and($page->revisions()->count())->toBe(1)
        ->and($page->revisions()->first()->name)->toBe('Old');
});

it('allows duplicating a page', function (): void {
    $page = Page::factory()->for($this->org)->create(['name' => 'Original', 'slug' => 'original']);

    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->post(route('pages.duplicate', $page));

    $response->assertRedirect();

    $copy = Page::query()->where('organization_id', $this->org->id)->where('slug', 'copy-of-original')->first();
    expect($copy)->not->toBeNull()
        ->and($copy->is_published)->toBeFalse();
});

it('allows deleting a page', function (): void {
    $page = Page::factory()->for($this->org)->create();

    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->delete(route('pages.destroy', $page));

    $response->assertRedirect(route('pages.index'));

    expect(Page::query()->find($page->id))->toBeNull();
});

it('allows previewing a page for editors', function (): void {
    $page = Page::factory()->for($this->org)->create(['name' => 'Draft', 'slug' => 'draft', 'is_published' => false]);

    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->get(route('pages.preview', $page));

    $response->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('pages/show')
            ->has('page')
            ->where('page.name', 'Draft')
        );
});

it('denies editing page from another organization', function (): void {
    $otherOrg = Organization::factory()->create();
    $page = Page::factory()->for($otherOrg)->create();

    $response = $this->actingAs($this->user)
        ->withSession($this->session)
        ->get(route('pages.edit', ['page' => $page->id]));

    $response->assertNotFound();
});

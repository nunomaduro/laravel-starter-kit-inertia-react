<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('serves robots.txt with sitemap line', function (): void {
    $response = $this->get(route('robots'));

    $response->assertOk();

    expect(str_contains((string) $response->headers->get('Content-Type'), 'text/plain'))->toBeTrue();
    $response->assertSee('User-agent: *')
        ->assertSee('Sitemap:');
});

it('renders legal terms page', function (): void {
    $response = $this->get(route('legal.terms'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('legal/terms'));
});

it('renders legal privacy page', function (): void {
    $response = $this->get(route('legal.privacy'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('legal/privacy'));
});

it('generates sitemap with expected urls', function (): void {
    $this->artisan('sitemap:generate')->assertSuccessful();

    $path = public_path('sitemap.xml');
    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)
        ->toContain('<loc>'.mb_rtrim(config('app.url'), '/').'</loc>')
        ->toContain('/contact')
        ->toContain('/login')
        ->toContain('/register')
        ->toContain('/legal/terms')
        ->toContain('/legal/privacy')
        ->toContain('/blog')
        ->toContain('/changelog')
        ->toContain('/help');
});

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use App\Services\PageDataSourceRegistry;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PageController extends Controller
{
    public function __construct(
        private readonly PageDataSourceRegistry $dataSourceRegistry
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Page::class);

        $pages = Page::query()
            ->latest('updated_at')
            ->get(['id', 'name', 'slug', 'is_published', 'updated_at']);

        return Inertia::render('pages/index', [
            'pages' => $pages,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Page::class);

        $templates = collect(config('pages.templates', []))
            ->map(fn (array $t, string $key): array => ['key' => $key, 'label' => $t['name'], 'data' => $t['data']])
            ->values()
            ->all();

        return Inertia::render('pages/edit', [
            'page' => null,
            'puckJson' => ['root' => (object) [], 'content' => []],
            'templates' => $templates,
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $page = new Page;
        $page->name = $validated['name'];
        $page->slug = $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['name']);
        $page->puck_json = $validated['puck_json'] ?? ['root' => (object) [], 'content' => []];
        $page->is_published = false;
        $page->save();

        return to_route('pages.edit', $page)->with('flash', ['status' => 'success', 'message' => 'Page created.']);
    }

    public function edit(Page $page): Response
    {
        $this->authorize('update', $page);

        return Inertia::render('pages/edit', [
            'page' => $page->only(['id', 'name', 'slug', 'puck_json', 'is_published', 'meta_description', 'meta_image']),
            'puckJson' => $page->puck_json ?? ['root' => (object) [], 'content' => []],
        ], [
            'ssr' => false,
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $page->revisions()->create([
            'puck_json' => $page->puck_json,
            'name' => $page->name,
            'slug' => $page->slug,
            'is_published' => $page->is_published,
        ]);

        $page->update([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'puck_json' => $request->validated('puck_json') ?? $page->puck_json,
            'is_published' => $request->validated('is_published', $page->is_published),
            'meta_description' => $request->validated('meta_description'),
            'meta_image' => $request->validated('meta_image'),
        ]);

        return to_route('pages.edit', $page)->with('flash', ['status' => 'success', 'message' => 'Page updated.']);
    }

    public function duplicate(Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        $copy = $page->replicate();
        $copy->name = 'Copy of '.$page->name;
        $copy->slug = \Illuminate\Support\Str::slug($copy->name);
        $copy->is_published = false;
        $copy->save();

        $uniqueSlug = $copy->slug;
        $n = 1;
        while (Page::query()->where('slug', $uniqueSlug)->where('id', '!=', $copy->id)->exists()) {
            $uniqueSlug = $copy->slug.'-'.($n++);
        }
        $copy->update(['slug' => $uniqueSlug]);

        return to_route('pages.edit', $copy)->with('flash', ['status' => 'success', 'message' => 'Page duplicated.']);
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);

        $page->delete();

        return to_route('pages.index')->with('flash', ['status' => 'success', 'message' => 'Page deleted.']);
    }

    public function preview(Request $request, Page $page): Response
    {
        $this->authorize('update', $page);

        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $puckJson = $page->puck_json ?? ['root' => (object) [], 'content' => []];
        $content = $puckJson['content'] ?? [];
        $user = $request->user();

        $content = array_map(function (array $item) use ($user, $organization): array {
            $props = $item['props'] ?? [];
            if (! isset($props['dataSource'])) {
                return $item;
            }
            $resolved = $this->dataSourceRegistry->resolve(
                (string) $props['dataSource'],
                $organization,
                $user,
                $props
            );
            $item['props'] = array_merge($props, ['data' => is_array($resolved) ? $resolved : $resolved->all()]);

            return $item;
        }, $content);

        $puckJson['content'] = $content;

        return Inertia::render('pages/show', [
            'page' => [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'puck_json' => $puckJson,
                'meta_description' => $page->meta_description,
                'meta_image' => $page->meta_image,
            ],
        ]);
    }
}

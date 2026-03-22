<?php

declare(strict_types=1);

namespace Modules\PageBuilder\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Modules\PageBuilder\Http\Requests\StorePageRequest;
use Modules\PageBuilder\Http\Requests\UpdatePageRequest;
use Modules\PageBuilder\Models\Page;
use Modules\PageBuilder\Services\PageDataSourceRegistry;

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
        $page->slug = $validated['slug'] ?? Str::slug($validated['name']);
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
        $copy->slug = Page::generateUniqueSlug(Str::slug($copy->name));
        $copy->is_published = false;
        $copy->save();

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

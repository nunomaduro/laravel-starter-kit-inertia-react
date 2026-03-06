<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageDataSourceRegistry;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PageViewController extends Controller
{
    public function __construct(
        private readonly PageDataSourceRegistry $dataSourceRegistry
    ) {}

    public function show(Request $request, string $slug): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $page = Page::query()->where('slug', $slug)->firstOrFail();

        $this->authorize('view', $page);

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

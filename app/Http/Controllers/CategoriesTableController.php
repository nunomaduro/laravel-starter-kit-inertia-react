<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CategoriesTableController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('categories/table', CategoryDataTable::inertiaProps($request));
    }

    public function create(): Response
    {
        return Inertia::render('categories/create', [
            'categories' => Category::query()->whereNull('parent_id')->orderBy('name')->get(['id', 'name'])->toArray(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return to_route('categories.table')->with('status', __('Category created.'));
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('categories/edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'type' => $category->type,
                'parent_id' => $category->parent_id,
            ],
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->where('id', '!=', $category->id)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray(),
        ]);
    }

    public function update(Category $category, UpdateCategoryRequest $request): RedirectResponse
    {
        $category->update($request->validated());

        return to_route('categories.table')->with('status', __('Category updated.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return to_route('categories.table')->with('status', __('Category deleted.'));
    }
}

---
title: RESTful Resource Methods
impact: HIGH
impactDescription: Standard CRUD operations following REST conventions
tags: controllers, rest, crud, conventions
---

## RESTful Resource Methods

**Impact: HIGH (Standard CRUD operations following REST conventions)**

Use resource controllers with standard RESTful methods for CRUD operations.

## Bad Example

```php
// Non-standard method names
class ArticleController extends Controller
{
    public function list() { /* ... */ }
    public function view($id) { /* ... */ }
    public function add() { /* ... */ }
    public function save(Request $request) { /* ... */ }
    public function modify($id) { /* ... */ }
    public function change(Request $request, $id) { /* ... */ }
    public function remove($id) { /* ... */ }
}

// Inconsistent routes
Route::get('/articles', [ArticleController::class, 'list']);
Route::get('/articles/{id}', [ArticleController::class, 'view']);
Route::get('/articles/add', [ArticleController::class, 'add']);
Route::post('/articles/save', [ArticleController::class, 'save']);
Route::get('/articles/{id}/modify', [ArticleController::class, 'modify']);
Route::post('/articles/{id}/change', [ArticleController::class, 'change']);
Route::post('/articles/{id}/remove', [ArticleController::class, 'remove']);
```

## Good Example

```php
// Standard resource controller
namespace App\Http\Controllers;

class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     * GET /articles
     */
    public function index()
    {
        $articles = Article::with('author')
            ->published()
            ->latest()
            ->paginate(20);

        return view('articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new article.
     * GET /articles/create
     */
    public function create()
    {
        $categories = Category::all();

        return view('articles.create', compact('categories'));
    }

    /**
     * Store a newly created article.
     * POST /articles
     */
    public function store(StoreArticleRequest $request)
    {
        $article = auth()->user()->articles()->create(
            $request->validated()
        );

        return redirect()
            ->route('articles.show', $article)
            ->with('success', 'Article created successfully');
    }

    /**
     * Display the specified article.
     * GET /articles/{article}
     */
    public function show(Article $article)
    {
        $article->load(['author', 'comments.user']);

        return view('articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified article.
     * GET /articles/{article}/edit
     */
    public function edit(Article $article)
    {
        $this->authorize('update', $article);
        $categories = Category::all();

        return view('articles.edit', compact('article', 'categories'));
    }

    /**
     * Update the specified article.
     * PUT/PATCH /articles/{article}
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->update($request->validated());

        return redirect()
            ->route('articles.show', $article)
            ->with('success', 'Article updated successfully');
    }

    /**
     * Remove the specified article.
     * DELETE /articles/{article}
     */
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        $article->delete();

        return redirect()
            ->route('articles.index')
            ->with('success', 'Article deleted successfully');
    }
}

// Simple resource route
Route::resource('articles', ArticleController::class);

// Partial resource
Route::resource('articles', ArticleController::class)
    ->only(['index', 'show']);

Route::resource('articles', ArticleController::class)
    ->except(['create', 'edit']);

// API resource (without create/edit)
Route::apiResource('articles', ArticleController::class);

// Nested resources
Route::resource('articles.comments', CommentController::class);
// Creates: articles/{article}/comments

// Shallow nesting
Route::resource('articles.comments', CommentController::class)->shallow();
// Nests only index, create, store
// Uses /comments/{comment} for show, update, destroy

// Named routes are automatic:
// articles.index, articles.create, articles.store
// articles.show, articles.edit, articles.update
// articles.destroy
```

## Why

- **Convention over configuration**: Standard names everyone understands
- **Automatic routing**: Single route declaration handles all methods
- **Named routes**: Automatic, predictable route names
- **HTTP verbs**: Proper use of GET, POST, PUT, DELETE
- **Framework support**: Form method spoofing, CSRF protection built-in
- **Team consistency**: All developers use the same patterns
- **Documentation**: Self-documenting API following REST conventions

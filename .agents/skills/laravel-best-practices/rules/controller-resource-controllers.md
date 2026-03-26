---
title: Use Resource Controllers
impact: HIGH
impactDescription: RESTful conventions and consistent routing
tags: controllers, rest, routing, conventions
---

## Use Resource Controllers

**Impact: HIGH (RESTful conventions and consistent routing)**

## Why It Matters

Resource controllers provide a consistent, RESTful structure for CRUD operations. They follow Laravel conventions, making code predictable and easier for other developers to understand.

## Bad Example

```php
// Inconsistent naming and structure
Route::get('/posts', [PostController::class, 'getAllPosts']);
Route::get('/posts/{id}', [PostController::class, 'getPost']);
Route::post('/posts/new', [PostController::class, 'createPost']);
Route::put('/posts/{id}/edit', [PostController::class, 'updatePost']);
Route::delete('/posts/{id}/delete', [PostController::class, 'removePost']);

// Non-standard controller methods
class PostController extends Controller
{
    public function getAllPosts() { }
    public function getPost($id) { }
    public function createPost() { }
    public function updatePost($id) { }
    public function removePost($id) { }
}
```

## Good Example

### Resource Route

```php
// Single line defines all 7 RESTful routes
Route::resource('posts', PostController::class);

// Generated routes:
// GET    /posts              index    posts.index
// GET    /posts/create       create   posts.create
// POST   /posts              store    posts.store
// GET    /posts/{post}       show     posts.show
// GET    /posts/{post}/edit  edit     posts.edit
// PUT    /posts/{post}       update   posts.update
// DELETE /posts/{post}       destroy  posts.destroy
```

### Resource Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $posts = Post::with('author')
            ->latest()
            ->paginate(15);

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::all();

        return view('posts.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        $post->load(['author', 'comments.user', 'tags']);

        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        $categories = Category::all();

        return view('posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}
```

### Partial Resource Routes

```php
// Only specific actions
Route::resource('posts', PostController::class)
    ->only(['index', 'show']);

// All except specific actions
Route::resource('posts', PostController::class)
    ->except(['destroy']);
```

### API Resource Controller

```php
// API routes (no create/edit - those are for forms)
Route::apiResource('posts', Api\PostController::class);

// Generated routes:
// GET    /posts          index
// POST   /posts          store
// GET    /posts/{post}   show
// PUT    /posts/{post}   update
// DELETE /posts/{post}   destroy
```

### Nested Resources

```php
// Nested resource routes
Route::resource('posts.comments', CommentController::class);

// Generated: /posts/{post}/comments/{comment}

// Shallow nesting (recommended)
Route::resource('posts.comments', CommentController::class)->shallow();

// Generated:
// /posts/{post}/comments       (index, store)
// /comments/{comment}          (show, update, destroy)
```

### Generate Resource Controller

```bash
# Generate with all methods
php artisan make:controller PostController --resource

# Generate with model binding
php artisan make:controller PostController --resource --model=Post

# Generate API controller
php artisan make:controller Api/PostController --api --model=Post
```

## Route Model Binding

```php
// Automatic model binding - Laravel resolves Post from {post}
public function show(Post $post): View
{
    // $post is automatically fetched or 404
    return view('posts.show', compact('post'));
}

// Custom binding key
// Route: /posts/{post:slug}
public function show(Post $post): View
{
    // Resolved by slug instead of id
}

// In model
class Post extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

## Benefits

- Consistent URL structure
- Predictable controller methods
- Automatic route model binding
- Easy to generate views (posts.index, posts.show, etc.)
- Clear conventions for team collaboration

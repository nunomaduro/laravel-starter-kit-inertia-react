---
title: Eager Loading Relationships
impact: CRITICAL
impactDescription: 10-100× query performance improvement
tags: eloquent, eager-loading, n+1, performance
---

## Eager Loading Relationships

**Impact: CRITICAL (10-100× query performance improvement)**

Always eager load relationships to prevent N+1 query problems and improve performance.

## Bad Example

```php
// N+1 query problem - executes 101 queries for 100 posts
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all(); // 1 query

        return view('posts.index', compact('posts'));
    }
}
```

In the view — N additional queries:

```blade
@foreach ($posts as $post)
    <p>{{ $post->author->name }}</p>  {{-- 1 query per post --}}
    <p>{{ $post->category->name }}</p> {{-- 1 query per post --}}
@endforeach
```

```php
// Also bad: loading in loop
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->comments->count(); // Query for each post
}
```

## Good Example

```php
// Eager load with 'with' - only 3 queries total
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['author', 'category'])->get();

        return view('posts.index', compact('posts'));
    }
}

// Nested eager loading
$posts = Post::with([
    'author',
    'category',
    'comments.user', // Nested relationship
])->get();

// Eager loading with constraints
$posts = Post::with([
    'comments' => function ($query) {
        $query->where('approved', true)
              ->orderBy('created_at', 'desc')
              ->limit(5);
    },
    'author:id,name,avatar', // Select specific columns
])->get();

// Conditional eager loading
$posts = Post::query()
    ->when($includeComments, fn($q) => $q->with('comments'))
    ->get();

// Eager load count without loading the relationship
$posts = Post::withCount('comments')->get();
// Access: $post->comments_count

// Multiple counts
$posts = Post::withCount(['comments', 'likes'])
    ->withSum('orderItems', 'quantity')
    ->get();

// Default eager loading in model
class Post extends Model
{
    protected $with = ['author']; // Always eager loaded
}

// Lazy eager loading when you already have models
$posts = Post::all();
$posts->load(['author', 'category']); // Load after the fact

// Prevent lazy loading in development (add to AppServiceProvider)
public function boot(): void
{
    Model::preventLazyLoading(!app()->isProduction());
}
```

## Why

- **Performance**: Reduces database queries from N+1 to just 2-3
- **Predictability**: Know exactly how many queries will run
- **Scalability**: Application performs consistently regardless of data size
- **Resource efficiency**: Less database connections and memory usage
- **Debugging**: Easier to identify and fix query issues
- **Prevention**: `preventLazyLoading()` catches N+1 issues during development

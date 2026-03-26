---
title: API Resources for Response Transformation
impact: HIGH
impactDescription: Consistent API responses and data transformation
tags: controllers, api, resources, transformation
---

## API Resources for Response Transformation

**Impact: HIGH (Consistent API responses and data transformation)**

Use API Resources to transform models into consistent JSON responses.

## Bad Example

```php
// Manual array transformation in controller
class UserController extends Controller
{
    public function show(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'created_at' => $user->created_at->toISOString(),
            // Forgot to include some fields? Inconsistent across endpoints?
        ]);
    }

    public function index()
    {
        $users = User::paginate(20);

        // Different transformation logic duplicated
        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // Missing avatar_url? Inconsistent!
                ];
            }),
            'meta' => [
                'total' => $users->total(),
                'page' => $users->currentPage(),
            ],
        ]);
    }
}
```

## Good Example

```php
// app/Http/Resources/UserResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar
                ? asset('storage/' . $this->avatar)
                : null,
            'email_verified' => !is_null($this->email_verified_at),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Conditional attributes
            'is_admin' => $this->when($request->user()?->isAdmin(), $this->is_admin),

            // Include relationships when loaded
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'profile' => new ProfileResource($this->whenLoaded('profile')),

            // Counts
            'posts_count' => $this->whenCounted('posts'),

            // Pivot data
            'role' => $this->whenPivotLoaded('role_user', function () {
                return $this->pivot->role;
            }),
        ];
    }

    /**
     * Add additional data to the response.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => '1.0',
            ],
        ];
    }
}

```

```php
// app/Http/Resources/UserCollection.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_users' => $this->collection->count(),
                'has_admins' => $this->collection->contains('is_admin', true),
            ],
        ];
    }
}

```

```php
// app/Http/Resources/PostResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => Str::limit($this->body, 150),
            'body' => $this->when(
                $request->routeIs('posts.show'),
                $this->body
            ),
            'published_at' => $this->published_at?->toISOString(),
            'is_published' => $this->isPublished(),

            // Always include author summary
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],

            // Full author resource when loaded
            'author_full' => new UserResource($this->whenLoaded('author')),

            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            'links' => [
                'self' => route('posts.show', $this->resource),
            ],
        ];
    }
}

```

```php
// Clean API controller
namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('profile')
            ->withCount('posts')
            ->paginate(20);

        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        $user->load(['posts' => fn($q) => $q->latest()->limit(5), 'profile']);

        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return new UserResource($user);
    }
}
```

Response structure is consistent:

```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": "https://...",
        "created_at": "2024-01-15T10:30:00.000000Z"
    },
    "meta": {
        "api_version": "1.0"
    }
}
```

## JSON:API Resources (Laravel 13+)

Laravel 13 adds first-party JSON:API resource support for APIs that need JSON:API specification compliance. Use these when building APIs that must follow the [JSON:API spec](https://jsonapi.org/); for standard APIs, `JsonResource` above remains the recommended approach.

```php
// JSON:API resources handle:
// - Resource object serialization with type/id/attributes
// - Relationship inclusion (included member)
// - Sparse fieldsets (?fields[users]=name,email)
// - JSON:API-compliant links and response headers

// Use JsonResource for most APIs.
// Use JSON:API resources only when clients require JSON:API compliance.
```

## Why

- **Consistency**: Same model always produces same JSON structure
- **Reusability**: Resource used across multiple endpoints
- **Conditional data**: Include data based on context
- **Relationships**: Handle nested resources elegantly
- **Pagination**: Automatic pagination metadata
- **API versioning**: Easy to create v2 resources
- **Documentation**: Resource classes document your API structure

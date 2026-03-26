---
name: laravel-inertia-react
description: Laravel + Inertia.js + React integration patterns. Use when building Inertia page components, handling forms with useForm, managing shared data, or implementing persistent layouts. Triggers on tasks involving Inertia.js, page props, form handling, or Laravel React integration.
license: MIT
metadata:
  author: AsyrafHussin
  version: "1.0.2"
  laravelVersion: "13.x"
  phpVersion: "8.3+"
---

# Laravel + Inertia.js + React

Comprehensive patterns for building modern monolithic applications with Laravel, Inertia.js, and React. Contains 30+ rules for seamless full-stack development.

## When to Apply

Reference these guidelines when:
- Creating Inertia page components
- Handling forms with useForm hook
- Managing shared data and authentication
- Implementing persistent layouts
- Navigating between pages

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Page Components | CRITICAL | `page-` |
| 2 | Forms & Validation | CRITICAL | `form-` |
| 3 | Navigation & Links | HIGH | `nav-` |
| 4 | Shared Data | HIGH | `shared-` |
| 5 | Layouts | MEDIUM | `layout-` |
| 6 | File Uploads | MEDIUM | `upload-` |
| 7 | Advanced Patterns | LOW | `advanced-` |

## Quick Reference

### 1. Page Components (CRITICAL)

- `page-props-typing` - Type page props from Laravel
- `page-component-structure` - Standard page component pattern
- `page-head-management` - Title and meta tags with Head
- `page-default-layout` - Assign layouts to pages

### 2. Forms & Validation (CRITICAL)

- `form-useform-basic` - Basic useForm usage
- `form-validation-errors` - Display Laravel validation errors
- `form-processing-state` - Handle form submission state
- `form-reset-preserve` - Reset vs preserve form data
- `form-nested-data` - Handle nested form data
- `form-transform` - Transform data before submit

### 3. Navigation & Links (HIGH)

- `nav-link-component` - Use Link for navigation
- `nav-preserve-state` - Preserve scroll and state
- `nav-partial-reloads` - Reload only what changed
- `nav-replace-history` - Replace vs push history

### 4. Shared Data (HIGH)

- `shared-auth-user` - Access authenticated user
- `shared-flash-messages` - Handle flash messages
- `shared-global-props` - Access global props
- `shared-typescript` - Type shared data

### 5. Layouts (MEDIUM)

- `layout-persistent` - Persistent layouts pattern
- `layout-nested` - Nested layouts
- `layout-default` - Default layout assignment
- `layout-conditional` - Conditional layouts

### 6. File Uploads (MEDIUM)

- `upload-basic` - Basic file upload
- `upload-progress` - Upload progress tracking
- `upload-multiple` - Multiple file uploads

### 7. Advanced Patterns (LOW)

- `advanced-polling` - Real-time polling
- `advanced-prefetch` - Prefetch pages
- `advanced-modal-pages` - Modal as pages
- `advanced-infinite-scroll` - Infinite scrolling

## Essential Patterns

### Page Component with TypeScript

```tsx
// resources/js/Pages/Posts/Index.tsx
import { Head, Link } from '@inertiajs/react'

interface Post {
  id: number
  title: string
  excerpt: string
  created_at: string
  author: {
    id: number
    name: string
  }
}

interface Props {
  posts: {
    data: Post[]
    links: { url: string | null; label: string; active: boolean }[]
  }
  filters: {
    search?: string
  }
}

export default function Index({ posts, filters }: Props) {
  return (
    <>
      <Head title="Posts" />

      <div className="container mx-auto py-8">
        <h1 className="text-2xl font-bold mb-6">Posts</h1>

        <div className="space-y-4">
          {posts.data.map((post) => (
            <article key={post.id} className="p-4 bg-white rounded-lg shadow">
              <Link href={route('posts.show', post.id)}>
                <h2 className="text-xl font-semibold hover:text-blue-600">
                  {post.title}
                </h2>
              </Link>
              <p className="text-gray-600 mt-2">{post.excerpt}</p>
              <p className="text-sm text-gray-400 mt-2">
                By {post.author.name}
              </p>
            </article>
          ))}
        </div>
      </div>
    </>
  )
}
```

### Form with useForm

```tsx
// resources/js/Pages/Posts/Create.tsx
import { Head, useForm, Link } from '@inertiajs/react'
import { FormEvent } from 'react'

interface Category {
  id: number
  name: string
}

interface Props {
  categories: Category[]
}

export default function Create({ categories }: Props) {
  const { data, setData, post, processing, errors, reset } = useForm({
    title: '',
    body: '',
    category_id: '',
  })

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault()
    post(route('posts.store'), {
      onSuccess: () => reset(),
    })
  }

  return (
    <>
      <Head title="Create Post" />

      <form onSubmit={handleSubmit} className="max-w-2xl mx-auto py-8">
        <div className="mb-4">
          <label htmlFor="title" className="block font-medium mb-1">
            Title
          </label>
          <input
            id="title"
            type="text"
            value={data.title}
            onChange={(e) => setData('title', e.target.value)}
            className="w-full border rounded px-3 py-2"
          />
          {errors.title && (
            <p className="text-red-500 text-sm mt-1">{errors.title}</p>
          )}
        </div>

        <div className="mb-4">
          <label htmlFor="category" className="block font-medium mb-1">
            Category
          </label>
          <select
            id="category"
            value={data.category_id}
            onChange={(e) => setData('category_id', e.target.value)}
            className="w-full border rounded px-3 py-2"
          >
            <option value="">Select a category</option>
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </select>
          {errors.category_id && (
            <p className="text-red-500 text-sm mt-1">{errors.category_id}</p>
          )}
        </div>

        <div className="mb-4">
          <label htmlFor="body" className="block font-medium mb-1">
            Content
          </label>
          <textarea
            id="body"
            value={data.body}
            onChange={(e) => setData('body', e.target.value)}
            rows={10}
            className="w-full border rounded px-3 py-2"
          />
          {errors.body && (
            <p className="text-red-500 text-sm mt-1">{errors.body}</p>
          )}
        </div>

        <div className="flex gap-4">
          <button
            type="submit"
            disabled={processing}
            className="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
          >
            {processing ? 'Creating...' : 'Create Post'}
          </button>

          <Link
            href={route('posts.index')}
            className="px-4 py-2 border rounded"
          >
            Cancel
          </Link>
        </div>
      </form>
    </>
  )
}
```

### Persistent Layout

```tsx
// resources/js/Layouts/AppLayout.tsx
import { Link, usePage } from '@inertiajs/react'
import { ReactNode } from 'react'

interface Props {
  children: ReactNode
}

export default function AppLayout({ children }: Props) {
  const { auth } = usePage().props as { auth: { user: { name: string } } }

  return (
    <div className="min-h-screen bg-gray-100">
      <nav className="bg-white shadow">
        <div className="container mx-auto px-4 py-3 flex justify-between">
          <Link href="/" className="font-bold">
            My App
          </Link>
          <span>Welcome, {auth.user.name}</span>
        </div>
      </nav>

      <main className="container mx-auto px-4 py-8">
        {children}
      </main>
    </div>
  )
}

// resources/js/Pages/Dashboard.tsx
import AppLayout from '@/Layouts/AppLayout'

export default function Dashboard() {
  return <h1>Dashboard</h1>
}

// Assign persistent layout
Dashboard.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>
```

### Laravel Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Posts/Index', [
            'posts' => Post::with('author:id,name')
                ->latest()
                ->paginate(10),
            'filters' => request()->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Posts/Create', [
            'categories' => Category::all(['id', 'name']),
        ]);
    }

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

    public function show(Post $post): Response
    {
        return Inertia::render('Posts/Show', [
            'post' => $post->load('author', 'category'),
        ]);
    }
}
```

### Shared Data (HandleInertiaRequests)

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
```

### Flash Messages Component

```tsx
// resources/js/Components/FlashMessages.tsx
import { usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'

export default function FlashMessages() {
  const { flash } = usePage().props as {
    flash: { success?: string; error?: string }
  }
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    if (flash.success || flash.error) {
      setVisible(true)
      const timer = setTimeout(() => setVisible(false), 3000)
      return () => clearTimeout(timer)
    }
  }, [flash])

  if (!visible) return null

  return (
    <div className="fixed top-4 right-4 z-50">
      {flash.success && (
        <div className="bg-green-500 text-white px-4 py-2 rounded shadow">
          {flash.success}
        </div>
      )}
      {flash.error && (
        <div className="bg-red-500 text-white px-4 py-2 rounded shadow">
          {flash.error}
        </div>
      )}
    </div>
  )
}
```

## How to Use

Read individual rule files for detailed explanations and code examples:

```
rules/form-useform-basic.md
rules/page-props-typing.md
rules/layout-persistent.md
```

## Project Structure

```
laravel-inertia-react/
├── SKILL.md                 # This file - overview and examples
├── README.md                # Quick reference guide
├── AGENTS.md                # Integration guide for AI agents
├── metadata.json            # Skill metadata and references
└── rules/
    ├── _sections.md         # Rule categories and priorities
    ├── _template.md         # Template for new rules
    ├── page-*.md            # Page component patterns (6 rules)
    ├── form-*.md            # Form handling patterns (8 rules)
    ├── nav-*.md             # Navigation patterns (5 rules)
    ├── shared-*.md          # Shared data patterns (4 rules)
    └── layout-*.md          # Layout patterns (1 rule)
```

## References

- [Inertia.js Documentation](https://inertiajs.com/) - Official Inertia.js docs
- [Laravel Documentation](https://laravel.com/docs) - Laravel framework docs
- [React Documentation](https://react.dev/) - Official React docs
- [Ziggy](https://github.com/tighten/ziggy) - Laravel route helper for JavaScript

## License

MIT License. This skill is provided as-is for educational and development purposes.

## Metadata

- **Version**: 1.0.1
- **Last Updated**: 2026-01-17
- **Maintainer**: Asyraf Hussin
- **Rule Count**: 24 rules across 6 categories
- **Tech Stack**: Laravel 10+, Inertia.js 1.0+, React 18+, TypeScript 5+

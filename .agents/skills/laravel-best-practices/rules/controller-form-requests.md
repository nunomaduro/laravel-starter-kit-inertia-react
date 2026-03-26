---
title: Form Requests in Controllers
impact: HIGH
impactDescription: Separates validation logic from controllers
tags: controllers, form-requests, validation
---

## Form Requests in Controllers

**Impact: HIGH (Separates validation logic from controllers)**

Move validation and authorization logic from controllers to dedicated Form Request classes.

## Bad Example

```php
// Validation logic cluttering the controller
class ArticleController extends Controller
{
    public function store(Request $request)
    {
        // Validation in controller
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:articles,title',
            'slug' => 'required|string|max:255|unique:articles,slug',
            'body' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'published_at' => 'nullable|date|after:now',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        // Authorization mixed in
        if (!auth()->user()->can('create', Article::class)) {
            abort(403);
        }

        $article = Article::create($validated);

        return redirect()->route('articles.show', $article);
    }

    public function update(Request $request, Article $article)
    {
        // Same validation duplicated
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:articles,title,' . $article->id,
            'slug' => 'required|string|max:255|unique:articles,slug,' . $article->id,
            'body' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        $article->update($validated);

        return redirect()->route('articles.show', $article);
    }
}
```

## Good Example

```php
// Store request
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Article::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:articles,title'],
            'slug' => ['required', 'string', 'max:255', 'unique:articles,slug'],
            'body' => ['required', 'string', 'min:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
            'published_at' => ['nullable', 'date', 'after:now'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'published_at' => 'publication date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.unique' => 'An article with this title already exists.',
            'body.min' => 'The article body must be at least :min characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ?? Str::slug($this->title),
        ]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Called after validation passes — use for side effects, not data modification
        // To add extra fields like user_id, do so in the controller:
        // Article::create([...$request->validated(), 'user_id' => $request->user()->id])
    }
}
```

```php
// Update request extending store
namespace App\Http\Requests;

class UpdateArticleRequest extends StoreArticleRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('article'));
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            ...parent::rules(),
            'title' => ['required', 'string', 'max:255', "unique:articles,title,{$article->id}"],
            'slug' => ['required', 'string', 'max:255', "unique:articles,slug,{$article->id}"],
            'published_at' => ['nullable', 'date'], // Remove 'after:now' for updates
        ];
    }
}
```

```php
// Clean controller
class ArticleController extends Controller
{
    public function store(StoreArticleRequest $request)
    {
        $article = Article::create($request->validated());

        return redirect()->route('articles.show', $article)
            ->with('success', 'Article created successfully');
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->update($request->validated());

        return redirect()->route('articles.show', $article)
            ->with('success', 'Article updated successfully');
    }
}
```

```bash
# Generate form request
php artisan make:request StoreArticleRequest
```

## Why

- **Separation of concerns**: Controllers handle HTTP, requests handle validation
- **Reusability**: Same validation rules in multiple places
- **Testability**: Validation logic tested independently
- **Authorization**: Access control in one place
- **Custom messages**: User-friendly error messages
- **Data preparation**: Transform input before validation
- **Thin controllers**: Controllers stay focused on their job

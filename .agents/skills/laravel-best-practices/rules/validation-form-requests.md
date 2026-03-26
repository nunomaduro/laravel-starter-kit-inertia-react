---
title: Form Request Validation
impact: HIGH
impactDescription: Centralized validation and authorization
tags: validation, form-requests, authorization
---

## Form Request Validation

**Impact: HIGH (Centralized validation and authorization)**

Encapsulate validation logic in Form Request classes for cleaner controllers and reusability.

## Bad Example

```php
// Validation cluttering the controller
class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'required|string|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'images' => 'array|max:5',
            'images.*' => 'image|max:2048',
            'attributes' => 'array',
            'attributes.*.name' => 'required|string',
            'attributes.*.value' => 'required|string',
        ], [
            'name.required' => 'Product name is required.',
            'sku.unique' => 'This SKU is already in use.',
        ]);

        // Authorization check also in controller
        if (!$request->user()->can('create', Product::class)) {
            abort(403);
        }

        $product = Product::create($validated);

        return redirect()->route('products.show', $product);
    }
}
```

## Good Example

```php
// Dedicated Form Request class
namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'images' => ['array', 'max:5'],
            'images.*' => ['image', 'max:2048', 'mimes:jpg,png,webp'],
            'attributes' => ['array'],
            'attributes.*.name' => ['required', 'string', 'max:50'],
            'attributes.*.value' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'sku' => 'SKU',
            'category_id' => 'category',
            'images.*' => 'image',
            'attributes.*.name' => 'attribute name',
            'attributes.*.value' => 'attribute value',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'sku.unique' => 'This SKU is already in use.',
            'images.max' => 'You can upload a maximum of :max images.',
            'price.max' => 'Price cannot exceed $999,999.99.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name),
            'price' => $this->price ? (float) $this->price : null,
        ]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Add computed values after validation
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException(
            'You are not authorized to create products.'
        );
    }
}

// Update request with different rules
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sku' => [
                'required',
                'string',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }
}

// Clean controller
class ProductController extends Controller
{
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created successfully');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully');
    }
}
```

## Why

- **Separation of concerns**: Validation logic separate from controller
- **Reusability**: Same request class used in multiple controllers
- **Authorization**: Single place for both validation and authorization
- **Custom messages**: User-friendly error messages
- **Testability**: Form requests can be tested independently
- **Clean controllers**: Controllers focus on business logic
- **Self-documenting**: Request class shows expected input structure

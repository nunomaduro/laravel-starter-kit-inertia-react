<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ValidPuckJson;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $this->user()?->canInOrganization('org.pages.manage', $page->organization) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organizationId = TenantContext::id();
        $pageId = $this->route('page')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages')->where('organization_id', $organizationId)->ignore($pageId),
            ],
            'puck_json' => ['nullable', 'array', new ValidPuckJson],
            'is_published' => ['sometimes', 'boolean'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_image' => ['nullable', 'string', 'max:500'],
        ];
    }
}

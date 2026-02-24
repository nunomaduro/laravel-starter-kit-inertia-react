<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ValidPuckJson;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canInOrganization('org.pages.manage') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organizationId = TenantContext::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages')->where('organization_id', $organizationId),
            ],
            'puck_json' => ['nullable', 'array', new ValidPuckJson],
        ];
    }
}

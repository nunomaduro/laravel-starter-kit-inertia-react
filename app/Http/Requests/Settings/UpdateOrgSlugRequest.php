<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\Organization;
use App\Rules\SlugAvailable;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateOrgSlugRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = TenantContext::get();

        return $organization instanceof Organization && $this->user()?->canInOrganization('org.settings.manage', $organization);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $organization = TenantContext::get();

        return [
            'slug' => ['required', 'string', new SlugAvailable($organization?->id)],
            'confirmed' => ['required', 'accepted'],
        ];
    }
}

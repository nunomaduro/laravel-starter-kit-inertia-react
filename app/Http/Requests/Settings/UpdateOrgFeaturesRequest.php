<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateOrgFeaturesRequest extends FormRequest
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
        return [
            'key' => ['required', 'string'],
            'override' => ['required', 'string', 'in:inherit,enabled,disabled'],
        ];
    }
}

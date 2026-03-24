<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class StoreOrgDomainRequest extends FormRequest
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
            'domain' => [
                'required',
                'string',
                'max:253',
                'regex:/^([a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i',
                'unique:organization_domains,domain',
            ],
        ];
    }
}

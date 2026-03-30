<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = TenantContext::get();

        return $organization !== null
            && $this->user()?->canInOrganization('org.email-templates.manage', $organization);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'integer', 'exists:crm_contacts,id'],
            'title' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'stage' => ['required', 'string', 'max:50'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        if (is_array($validated)) {
            $validated['organization_id'] = TenantContext::id();
            $validated['status'] = 'open';
        }

        return $validated;
    }
}

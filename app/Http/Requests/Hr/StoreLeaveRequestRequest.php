<?php

declare(strict_types=1);

namespace App\Http\Requests\Hr;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class StoreLeaveRequestRequest extends FormRequest
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
            'employee_id' => ['required', 'integer', 'exists:hr_employees,id'],
            'type' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
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
            $validated['status'] = 'pending';
        }

        return $validated;
    }
}

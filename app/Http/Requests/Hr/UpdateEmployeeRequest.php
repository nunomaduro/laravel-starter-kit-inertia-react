<?php

declare(strict_types=1);

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEmployeeRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'department_id' => ['nullable', 'integer', 'exists:hr_departments,id'],
            'status' => ['nullable', 'string', 'in:active,inactive,terminated'],
        ];
    }
}

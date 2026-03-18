<?php

declare(strict_types=1);

namespace Modules\Dashboards\Http\Requests;

use App\Rules\ValidPuckJson;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'puck_json' => ['nullable', 'array', new ValidPuckJson],
            'is_default' => ['boolean'],
            'refresh_interval' => ['nullable', 'integer', 'min:5', 'max:3600'],
        ];
    }
}

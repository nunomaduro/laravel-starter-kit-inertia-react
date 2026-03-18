<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Requests;

use App\Rules\ValidPuckJson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Reports\Enums\OutputFormat;

final class UpdateReportRequest extends FormRequest
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
            'schedule' => ['nullable', 'string', 'max:255'],
            'output_format' => ['required', Rule::enum(OutputFormat::class)],
        ];
    }
}

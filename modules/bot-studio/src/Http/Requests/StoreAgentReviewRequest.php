<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAgentReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Requests;

use App\Enums\VisibilityEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAgentDefinitionRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'system_prompt' => ['sometimes', 'string'],
            'model' => ['nullable', 'string', 'max:50'],
            'temperature' => ['nullable', 'numeric', 'between:0,1'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'enabled_tools' => ['nullable', 'array'],
            'enabled_tools.*' => ['string'],
            'conversation_starters' => ['nullable', 'array'],
            'conversation_starters.*' => ['string', 'max:255'],
            'wizard_answers' => ['nullable', 'array'],
            'visibility' => ['nullable', Rule::enum(VisibilityEnum::class)],
        ];
    }
}

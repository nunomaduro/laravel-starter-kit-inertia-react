<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Requests;

use App\Enums\VisibilityEnum;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAgentDefinitionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'system_prompt' => ['required', 'string'],
            'model' => ['nullable', 'string', 'max:50'],
            'temperature' => ['nullable', 'numeric', 'between:0,1'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'enabled_tools' => ['nullable', 'array'],
            'enabled_tools.*' => ['string'],
            'conversation_starters' => ['nullable', 'array'],
            'conversation_starters.*' => ['string', 'max:255'],
            'wizard_answers' => ['nullable', 'array'],
            'visibility' => ['nullable', 'string', Rule::enum(VisibilityEnum::class)],
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
            $validated['created_by'] = $this->user()?->id;
        }

        return $validated;
    }
}

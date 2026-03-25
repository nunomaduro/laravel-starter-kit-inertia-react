<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\AgentConversation;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'messages' => ['required', 'array'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant,system'],
            'context' => ['nullable', 'array'],
            'context.page' => ['nullable', 'string'],
            'context.entity_type' => ['nullable', 'string'],
            'context.entity_id' => ['nullable', 'integer'],
            'context.entity_name' => ['nullable', 'string'],
            'conversation_id' => ['nullable', 'string', 'uuid', function (string $attr, string $value, Closure $fail): void {
                $user = $this->user();
                if ($user === null) {
                    $fail('Unauthenticated.');

                    return;
                }

                $exists = AgentConversation::query()
                    ->where('id', $value)
                    ->where('user_id', $user->id)
                    ->exists();
                if (! $exists) {
                    $fail('The selected conversation is invalid.');
                }
            }],
        ];
    }
}

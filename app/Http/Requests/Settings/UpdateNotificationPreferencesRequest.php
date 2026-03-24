<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateNotificationPreferencesRequest extends FormRequest
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
        /** @var array<string, array{label: string, channels: list<string>}> $types */
        $types = config('notification-types', []);
        $typeKeys = array_keys($types);

        return [
            'preferences' => ['required', 'array'],
            'preferences.*.key' => ['required', 'string', 'in:'.implode(',', $typeKeys)],
            'preferences.*.via_database' => ['required', 'boolean'],
            'preferences.*.via_email' => ['required', 'boolean'],
        ];
    }
}

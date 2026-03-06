<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\User;
use App\Rules\ValidEmail;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class BatchUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create users') ?? false
            || $this->user()?->can('edit users') ?? false
            || $this->user()?->can('delete users') ?? false;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'create' => ['sometimes', 'array'],
            'create.*.name' => ['required_with:create.*', 'string', 'max:255'],
            'create.*.email' => [
                'required_with:create.*',
                'string',
                'lowercase',
                'max:255',
                'email',
                new ValidEmail,
                Rule::unique(User::class),
            ],
            'create.*.password' => [
                'required_with:create.*',
                'string',
                Password::defaults(),
            ],
            'update' => ['sometimes', 'array'],
            'update.*.id' => ['required_with:update.*', 'integer', 'exists:users,id'],
            'update.*.name' => ['sometimes', 'string', 'max:255'],
            'update.*.email' => [
                'sometimes',
                'string',
                'lowercase',
                'max:255',
                'email',
                new ValidEmail,
                function (string $attribute, mixed $value, Closure $fail): void {
                    $segments = explode('.', $attribute);
                    $idx = $segments[1] ?? null;
                    if ($idx === null) {
                        return;
                    }

                    $id = $this->input(sprintf('update.%s.id', $idx));
                    if (User::query()->where('email', $value)->whereKeyNot($id)->exists()) {
                        $fail(__('validation.unique', ['attribute' => 'email']));
                    }
                },
            ],
            'delete' => ['sometimes', 'array'],
            'delete.*' => ['integer', 'exists:users,id'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Actions\BatchUpdateUsersAction;
use Illuminate\Foundation\Http\FormRequest;

final class BatchUpdateUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($user->can('bypass-permissions')) {
            return true;
        }

        return config('tenancy.enabled', true)
            && $user->canInOrganization('org.members.view');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
            'column' => ['required', 'string', 'in:'.implode(',', BatchUpdateUsersAction::ALLOWED_COLUMNS)],
            'value' => ['required'],
        ];
    }
}

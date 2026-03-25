<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDataTableSavedViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }

        // System views require the 'manage system views' permission
        if ($this->boolean('is_system')) {
            return $this->user()->can('manage system views');
        }

        // Shared views are intentionally open to all authenticated users.
        // Any team member can share views with their organization — no
        // additional permission gate is required by design.
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'table_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'filters' => ['nullable', 'array'],
            'sort' => ['nullable', 'string', 'max:500'],
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string'],
            'column_order' => ['nullable', 'array'],
            'column_order.*' => ['string'],
            'is_default' => ['nullable', 'boolean'],
            'is_shared' => ['nullable', 'boolean'],
            'is_system' => ['nullable', 'boolean'],
        ];
    }
}

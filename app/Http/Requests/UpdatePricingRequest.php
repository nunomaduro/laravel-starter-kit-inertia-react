<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'room_types' => ['required', 'array'],
            'room_types.*.id' => ['required', 'string', 'exists:room_types,id'],
            'room_types.*.day_prices' => ['nullable', 'array'],
            'room_types.*.day_prices.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'room_types.*.day_prices.*.price_per_night' => ['required', 'integer', 'min:1'],
            'room_types.*.seasonal_prices' => ['nullable', 'array'],
            'room_types.*.seasonal_prices.*.name' => ['required', 'string'],
            'room_types.*.seasonal_prices.*.start_date' => ['required', 'date'],
            'room_types.*.seasonal_prices.*.end_date' => ['required', 'date', 'after:room_types.*.seasonal_prices.*.start_date'],
            'room_types.*.seasonal_prices.*.price_per_night' => ['required', 'integer', 'min:1'],
            'room_types.*.special_date_prices' => ['nullable', 'array'],
            'room_types.*.special_date_prices.*.date' => ['required', 'date'],
            'room_types.*.special_date_prices.*.price_per_night' => ['required', 'integer', 'min:1'],
            'room_types.*.special_date_prices.*.label' => ['nullable', 'string'],
        ];
    }
}

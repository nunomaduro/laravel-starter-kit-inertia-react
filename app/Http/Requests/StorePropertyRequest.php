<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePropertyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', Rule::enum(PropertyType::class)],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'country' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string'],
            'cancellation_policy' => ['nullable', 'string'],
            'room_types' => ['nullable', 'array'],
            'room_types.*.name' => ['required_with:room_types', 'string'],
            'room_types.*.description' => ['nullable', 'string'],
            'room_types.*.max_guests' => ['required_with:room_types', 'integer', 'min:1'],
            'room_types.*.base_price_per_night' => ['required_with:room_types', 'integer', 'min:1'],
            'room_types.*.min_nights' => ['nullable', 'integer', 'min:1'],
            'room_types.*.max_nights' => ['nullable', 'integer', 'min:1'],
            'room_types.*.total_rooms' => ['required_with:room_types', 'integer', 'min:1'],
        ];
    }
}

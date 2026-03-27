<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\Support\Str;

final readonly class UpdateProperty
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Property $property, array $attributes): Property
    {
        if (isset($attributes['name']) && $attributes['name'] !== $property->name) {
            /** @var string $newName */
            $newName = $attributes['name'];
            $attributes['slug'] = $this->generateUniqueSlug($newName, $property->id);
        }

        $resetFields = ['name', 'type', 'address', 'city', 'country'];
        $shouldResetStatus = false;

        foreach ($resetFields as $field) {
            if (isset($attributes[$field]) && $attributes[$field] !== $property->getAttribute($field)) {
                $shouldResetStatus = true;

                break;
            }
        }

        if ($shouldResetStatus) {
            $attributes['status'] = PropertyStatus::Pending;
        }

        $property->update($attributes);

        return $property->refresh();
    }

    private function generateUniqueSlug(string $name, string $excludeId): string
    {
        $slug = Str::slug($name);

        if (! Property::query()->where('slug', $slug)->where('id', '!=', $excludeId)->exists()) {
            return $slug;
        }

        return $slug.'-'.Str::random(6);
    }
}

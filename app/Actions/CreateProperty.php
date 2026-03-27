<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateProperty
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $host, array $attributes): Property
    {
        return DB::transaction(function () use ($host, $attributes): Property {
            /** @var string $name */
            $name = $attributes['name'];
            $slug = $this->generateUniqueSlug($name);

            return Property::query()->create([
                ...$attributes,
                'host_id' => $host->id,
                'slug' => $slug,
                'status' => PropertyStatus::Pending,
            ]);
        });
    }

    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

        if (! Property::query()->where('slug', $slug)->exists()) {
            return $slug;
        }

        return $slug.'-'.Str::random(6);
    }
}

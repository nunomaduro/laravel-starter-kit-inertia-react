<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Property;
use Inertia\Inertia;
use Inertia\Response;

final readonly class HomepageController
{
    public function __invoke(): Response
    {
        $featuredProperties = Property::approved()
            ->featured()
            ->with(['media', 'roomTypes', 'reviews'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Property $property) => [
                ...$property->toArray(),
                'cover_image' => $property->getFirstMediaUrl('images', 'thumb') ?: null,
                'min_price' => $property->roomTypes->min('base_price_per_night'),
                'average_rating' => round($property->reviews->avg('rating') ?? 0, 1),
                'reviews_count' => $property->reviews->count(),
            ]);

        return Inertia::render('home', [
            'featuredProperties' => $featuredProperties,
        ]);
    }
}

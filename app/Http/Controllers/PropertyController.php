<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Property;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class PropertyController
{
    public function index(Request $request): Response
    {
        $query = Property::approved()
            ->with(['media', 'roomTypes', 'reviews']);

        if ($request->filled('location')) {
            $location = $request->string('location')->toString();
            $query->where(function ($q) use ($location): void {
                $q->where('city', 'like', "%{$location}%")
                    ->orWhere('country', 'like', "%{$location}%")
                    ->orWhere('address', 'like', "%{$location}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('amenities')) {
            /** @var array<int, string> $amenities */
            $amenities = $request->input('amenities');
            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        if ($request->filled('min_price')) {
            $query->whereHas('roomTypes', function ($q) use ($request): void {
                $q->where('base_price_per_night', '>=', $request->integer('min_price'));
            });
        }

        if ($request->filled('max_price')) {
            $query->whereHas('roomTypes', function ($q) use ($request): void {
                $q->where('base_price_per_night', '<=', $request->integer('max_price'));
            });
        }

        if ($request->filled('guests')) {
            $query->whereHas('roomTypes', function ($q) use ($request): void {
                $q->where('max_guests', '>=', $request->integer('guests'));
            });
        }

        $properties = $query->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Property $property) => [
                ...$property->toArray(),
                'cover_image' => $property->getFirstMediaUrl('images', 'thumb') ?: null,
                'min_price' => $property->roomTypes->min('base_price_per_night'),
                'average_rating' => round($property->reviews->avg('rating') ?? 0, 1),
                'reviews_count' => $property->reviews->count(),
            ]);

        return Inertia::render('search/index', [
            'properties' => $properties,
            'filters' => $request->only(['location', 'type', 'amenities', 'min_price', 'max_price', 'guests', 'check_in', 'check_out']),
            'amenities' => [
                ['key' => 'pool', 'name' => 'Pool'],
                ['key' => 'wifi', 'name' => 'WiFi'],
                ['key' => 'parking', 'name' => 'Parking'],
                ['key' => 'restaurant', 'name' => 'Restaurant'],
                ['key' => 'gym', 'name' => 'Gym'],
                ['key' => 'spa', 'name' => 'Spa'],
                ['key' => 'beach_access', 'name' => 'Beach Access'],
                ['key' => 'air_conditioning', 'name' => 'Air Conditioning'],
            ],
        ]);
    }

    public function show(Property $property): Response
    {
        $property->load(['media', 'roomTypes', 'host', 'reviews']);

        $reviews = $property->reviews()
            ->with('guest')
            ->latest()
            ->paginate(10);

        $user = auth()->user();
        $isWishlisted = false;
        $canReview = false;

        if ($user) {
            $isWishlisted = Wishlist::query()
                ->where('user_id', $user->id)
                ->where('property_id', $property->id)
                ->exists();

            $canReview = $property->bookings()
                ->where('guest_id', $user->id)
                ->where('status', BookingStatus::Completed)
                ->whereDoesntHave('review')
                ->exists();
        }

        $propertyData = [
            ...$property->toArray(),
            'cover_image' => $property->getFirstMediaUrl('images', 'preview') ?: null,
            'images' => $property->getMedia('images')->map(fn ($media) => [
                'id' => $media->id,
                'path' => $media->getUrl(),
                'preview' => $media->getUrl('preview'),
                'thumb' => $media->getUrl('thumb'),
                'order' => $media->order_column,
            ])->values(),
            'average_rating' => round($property->reviews->avg('rating') ?? 0, 1),
            'reviews_count' => $property->reviews->count(),
            'min_price' => $property->roomTypes->min('base_price_per_night'),
        ];

        return Inertia::render('properties/show', [
            'property' => $propertyData,
            'reviews' => $reviews,
            'isWishlisted' => $isWishlisted,
            'canReview' => $canReview,
        ]);
    }
}

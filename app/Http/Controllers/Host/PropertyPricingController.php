<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Http\Requests\UpdatePricingRequest;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final readonly class PropertyPricingController
{
    public function show(Property $property): Response
    {
        Gate::authorize('update', $property);

        $property->load(['roomTypes.dayPrices', 'roomTypes.seasonalPrices', 'roomTypes.specialDatePrices']);

        return Inertia::render('host/properties/pricing', [
            'property' => $property,
        ]);
    }

    public function update(UpdatePricingRequest $request, Property $property): RedirectResponse
    {
        Gate::authorize('update', $property);

        /** @var array{room_types: array<int, array<string, mixed>>} $validated */
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            foreach ($validated['room_types'] as $roomTypeData) {
                /** @var RoomType $roomType */
                $roomType = RoomType::query()->findOrFail($roomTypeData['id']);

                if (isset($roomTypeData['day_prices']) && is_array($roomTypeData['day_prices'])) {
                    $roomType->dayPrices()->delete();
                    foreach ($roomTypeData['day_prices'] as $dayPrice) {
                        /** @var array<string, mixed> $dayPrice */
                        $roomType->dayPrices()->create($dayPrice);
                    }
                }

                if (isset($roomTypeData['seasonal_prices']) && is_array($roomTypeData['seasonal_prices'])) {
                    $roomType->seasonalPrices()->delete();
                    foreach ($roomTypeData['seasonal_prices'] as $seasonalPrice) {
                        /** @var array<string, mixed> $seasonalPrice */
                        $roomType->seasonalPrices()->create($seasonalPrice);
                    }
                }

                if (isset($roomTypeData['special_date_prices']) && is_array($roomTypeData['special_date_prices'])) {
                    $roomType->specialDatePrices()->delete();
                    foreach ($roomTypeData['special_date_prices'] as $specialDatePrice) {
                        /** @var array<string, mixed> $specialDatePrice */
                        $roomType->specialDatePrices()->create($specialDatePrice);
                    }
                }
            }
        });

        return redirect()->back();
    }
}

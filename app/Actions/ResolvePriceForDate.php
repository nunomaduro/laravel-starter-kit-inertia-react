<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DayOfWeek;
use App\Models\RoomType;
use App\Models\RoomTypePrice;
use App\Models\SeasonalPrice;
use App\Models\SpecialDatePrice;
use Carbon\CarbonInterface;

final readonly class ResolvePriceForDate
{
    public function handle(RoomType $roomType, CarbonInterface $date): int
    {
        $specialPrice = SpecialDatePrice::query()
            ->where('room_type_id', $roomType->id)
            ->whereDate('date', $date)
            ->first();

        if ($specialPrice instanceof SpecialDatePrice) {
            return $specialPrice->price_per_night;
        }

        $seasonalPrice = SeasonalPrice::query()
            ->where('room_type_id', $roomType->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->latest()
            ->first();

        if ($seasonalPrice instanceof SeasonalPrice) {
            return $seasonalPrice->price_per_night;
        }

        $dayPrice = RoomTypePrice::query()
            ->where('room_type_id', $roomType->id)
            ->where('day_of_week', DayOfWeek::from($date->dayOfWeek))
            ->first();

        if ($dayPrice instanceof RoomTypePrice) {
            return $dayPrice->price_per_night;
        }

        return $roomType->base_price_per_night;
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\RoomTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read string $property_id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read int $max_guests
 * @property-read int $base_price_per_night
 * @property-read int $min_nights
 * @property-read int|null $max_nights
 * @property-read int $total_rooms
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class RoomType extends Model
{
    /** @use HasFactory<RoomTypeFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'property_id' => 'string',
            'name' => 'string',
            'description' => 'string',
            'max_guests' => 'integer',
            'base_price_per_night' => 'integer',
            'min_nights' => 'integer',
            'max_nights' => 'integer',
            'total_rooms' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return HasMany<RoomTypePrice, $this>
     */
    public function dayPrices(): HasMany
    {
        return $this->hasMany(RoomTypePrice::class);
    }

    /**
     * @return HasMany<SeasonalPrice, $this>
     */
    public function seasonalPrices(): HasMany
    {
        return $this->hasMany(SeasonalPrice::class);
    }

    /**
     * @return HasMany<SpecialDatePrice, $this>
     */
    public function specialDatePrices(): HasMany
    {
        return $this->hasMany(SpecialDatePrice::class);
    }

    /**
     * @return HasMany<BlockedDate, $this>
     */
    public function blockedDates(): HasMany
    {
        return $this->hasMany(BlockedDate::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

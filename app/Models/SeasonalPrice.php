<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SeasonalPriceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $room_type_id
 * @property-read string $name
 * @property-read CarbonInterface $start_date
 * @property-read CarbonInterface $end_date
 * @property-read int $price_per_night
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class SeasonalPrice extends Model
{
    /** @use HasFactory<SeasonalPriceFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'room_type_id' => 'string',
            'name' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
            'price_per_night' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<RoomType, $this>
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}

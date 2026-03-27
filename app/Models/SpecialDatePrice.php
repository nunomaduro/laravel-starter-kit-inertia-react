<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SpecialDatePriceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $room_type_id
 * @property-read CarbonInterface $date
 * @property-read int $price_per_night
 * @property-read string|null $label
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class SpecialDatePrice extends Model
{
    /** @use HasFactory<SpecialDatePriceFactory> */
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
            'date' => 'date',
            'price_per_night' => 'integer',
            'label' => 'string',
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

<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $booking_id
 * @property-read string $guest_id
 * @property-read string $property_id
 * @property-read int $rating
 * @property-read string $comment
 * @property-read string|null $host_response
 * @property-read CarbonInterface|null $host_responded_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'booking_id' => 'string',
            'guest_id' => 'string',
            'property_id' => 'string',
            'rating' => 'integer',
            'comment' => 'string',
            'host_response' => 'string',
            'host_responded_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

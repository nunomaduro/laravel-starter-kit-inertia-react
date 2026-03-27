<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\CancelledBy;
use Carbon\CarbonInterface;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read string $id
 * @property-read string $guest_id
 * @property-read string $property_id
 * @property-read string $room_type_id
 * @property-read CarbonInterface $check_in
 * @property-read CarbonInterface $check_out
 * @property-read int $guests_count
 * @property-read BookingStatus $status
 * @property-read CancelledBy|null $cancelled_by
 * @property-read string|null $cancellation_reason
 * @property-read int $total_price
 * @property-read int $commission_amount
 * @property-read int $host_payout
 * @property-read array<string, mixed>|null $price_breakdown
 * @property-read string|null $notes
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'guest_id' => 'string',
            'property_id' => 'string',
            'room_type_id' => 'string',
            'check_in' => 'date',
            'check_out' => 'date',
            'guests_count' => 'integer',
            'status' => BookingStatus::class,
            'cancelled_by' => CancelledBy::class,
            'cancellation_reason' => 'string',
            'total_price' => 'integer',
            'commission_amount' => 'integer',
            'host_payout' => 'integer',
            'price_breakdown' => 'array',
            'notes' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
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

    /**
     * @return BelongsTo<RoomType, $this>
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * @return HasOne<Review, $this>
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * @return HasOne<Conversation, $this>
     */
    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function nights(): int
    {
        return (int) $this->check_in->diffInDays($this->check_out);
    }
}

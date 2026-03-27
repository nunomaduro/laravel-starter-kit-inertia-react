<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use Carbon\CarbonInterface;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read string $id
 * @property-read string $host_id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string $description
 * @property-read PropertyType $type
 * @property-read string $address
 * @property-read string $city
 * @property-read string $country
 * @property-read string|null $latitude
 * @property-read string|null $longitude
 * @property-read array<int, string>|null $amenities
 * @property-read PropertyStatus $status
 * @property-read bool $is_featured
 * @property-read string|null $cancellation_policy
 * @property-read CarbonInterface|null $deleted_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Property extends Model implements HasMedia
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;
    use SoftDeletes;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public');

        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300);

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'host_id' => 'string',
            'name' => 'string',
            'slug' => 'string',
            'description' => 'string',
            'type' => PropertyType::class,
            'address' => 'string',
            'city' => 'string',
            'country' => 'string',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'amenities' => 'array',
            'status' => PropertyStatus::class,
            'is_featured' => 'boolean',
            'cancellation_policy' => 'string',
            'deleted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * @return HasMany<PropertyImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    /**
     * @return HasMany<RoomType, $this>
     */
    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    /**
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PropertyStatus::Approved);
    }

    /**
     * @param  Builder<Property>  $query
     * @return Builder<Property>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}

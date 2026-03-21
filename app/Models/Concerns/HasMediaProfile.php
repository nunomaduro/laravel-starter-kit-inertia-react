<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Media profile: avatar collections, conversions, and URL accessors.
 *
 * @mixin \App\Models\User
 */
trait HasMediaProfile
{
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('avatar')
            ->fit(Fit::Crop, 48, 48)
            ->nonQueued();

        $this->addMediaConversion('profile')
            ->performOnCollections('avatar')
            ->fit(Fit::Crop, 192, 192)
            ->nonQueued();
    }

    /**
     * Avatar URL (thumb conversion) for nav/header, or null when no avatar.
     */
    protected function avatar(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('avatar', 'thumb') ?: null);
    }

    /**
     * Avatar URL (profile conversion) for profile/settings preview, or null when no avatar.
     */
    protected function avatarProfile(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('avatar', 'profile') ?: null);
    }
}

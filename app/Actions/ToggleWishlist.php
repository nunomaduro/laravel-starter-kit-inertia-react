<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Property;
use App\Models\User;
use App\Models\Wishlist;

final readonly class ToggleWishlist
{
    public function handle(User $user, Property $property): bool
    {
        $wishlist = Wishlist::query()
            ->where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();

        if ($wishlist instanceof Wishlist) {
            $wishlist->delete();

            return false;
        }

        Wishlist::query()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        return true;
    }
}

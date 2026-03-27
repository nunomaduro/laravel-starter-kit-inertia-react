<?php

declare(strict_types=1);

use App\Actions\ToggleWishlist;
use App\Models\Property;
use App\Models\User;
use App\Models\Wishlist;

it('adds a property to wishlist', function (): void {
    $user = User::factory()->create();
    $property = Property::factory()->create();

    $result = app(ToggleWishlist::class)->handle($user, $property);

    expect($result)->toBeTrue();
    $this->assertDatabaseHas('wishlists', [
        'user_id' => $user->id,
        'property_id' => $property->id,
    ]);
});

it('removes a property from wishlist', function (): void {
    $user = User::factory()->create();
    $property = Property::factory()->create();

    Wishlist::factory()->create([
        'user_id' => $user->id,
        'property_id' => $property->id,
    ]);

    $result = app(ToggleWishlist::class)->handle($user, $property);

    expect($result)->toBeFalse();
    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $user->id,
        'property_id' => $property->id,
    ]);
});

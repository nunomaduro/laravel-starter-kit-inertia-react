<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;

final readonly class CreateConversation
{
    public function handle(User $guest, Property $property, ?Booking $booking = null): Conversation
    {
        $existing = Conversation::query()
            ->where('guest_id', $guest->id)
            ->where('property_id', $property->id)
            ->first();

        if ($existing instanceof Conversation) {
            return $existing;
        }

        return Conversation::query()->create([
            'guest_id' => $guest->id,
            'host_id' => $property->host_id,
            'property_id' => $property->id,
            'booking_id' => $booking?->id,
        ]);
    }
}

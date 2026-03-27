<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Property;
use App\Models\Review;
use App\Models\RoomType;
use App\Models\RoomTypePrice;
use App\Models\SeasonalPrice;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Throwable;

final class DatabaseSeeder extends Seeder
{
    /** @var list<string> */
    private const array UNSPLASH_IMAGES = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
        'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800',
        'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800',
        'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800',
        'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800',
        'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800',
        'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800',
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800',
        'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?w=800',
        'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800',
        'https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=800',
        'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800',
    ];

    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $hosts = User::factory()->host()->count(3)->create();

        $guests = User::factory()->count(10)->create();

        foreach ($hosts as $host) {
            $propertyCount = fake()->numberBetween(2, 4);

            $properties = Property::factory()
                ->count($propertyCount)
                ->create(['host_id' => $host->id]);

            foreach ($properties as $property) {
                try {
                    $imageUrls = fake()->randomElements(self::UNSPLASH_IMAGES, fake()->numberBetween(2, 3));
                    foreach ($imageUrls as $imageUrl) {
                        $property->addMediaFromUrl($imageUrl)->toMediaCollection('images');
                    }
                } catch (Throwable) {
                    // Skip media if network is unavailable
                }

                $roomTypeCount = fake()->numberBetween(1, 3);
                $roomTypes = RoomType::factory()
                    ->count($roomTypeCount)
                    ->create(['property_id' => $property->id]);

                foreach ($roomTypes as $roomType) {
                    $daysToPrice = fake()->randomElements([0, 1, 2, 3, 4, 5, 6], fake()->numberBetween(2, 4));
                    foreach ($daysToPrice as $day) {
                        RoomTypePrice::factory()->create([
                            'room_type_id' => $roomType->id,
                            'day_of_week' => $day,
                        ]);
                    }

                    if (fake()->boolean(50)) {
                        SeasonalPrice::factory()->create([
                            'room_type_id' => $roomType->id,
                        ]);
                    }
                }
            }
        }

        $allProperties = Property::all();
        $allRoomTypes = RoomType::all();

        foreach ($guests as $guest) {
            $bookingCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $bookingCount; $i++) {
                $property = $allProperties->random();
                $roomType = $allRoomTypes->where('property_id', $property->id)->random();

                $statuses = ['pending', 'approved', 'completed', 'cancelled', 'declined'];
                $status = fake()->randomElement($statuses);

                $booking = Booking::factory()
                    ->when($status === 'approved', fn ($f) => $f->approved())
                    ->when($status === 'completed', fn ($f) => $f->completed())
                    ->when($status === 'cancelled', fn ($f) => $f->cancelled())
                    ->when($status === 'declined', fn ($f) => $f->declined())
                    ->create([
                        'guest_id' => $guest->id,
                        'property_id' => $property->id,
                        'room_type_id' => $roomType->id,
                    ]);

                if ($status === 'completed' && fake()->boolean(70)) {
                    Review::factory()
                        ->when(fake()->boolean(40), fn ($f) => $f->withHostResponse())
                        ->create([
                            'booking_id' => $booking->id,
                            'guest_id' => $guest->id,
                            'property_id' => $property->id,
                        ]);
                }

                if (fake()->boolean(40)) {
                    $conversation = Conversation::factory()->create([
                        'property_id' => $property->id,
                        'booking_id' => $booking->id,
                        'guest_id' => $guest->id,
                        'host_id' => $property->host_id,
                    ]);

                    $messageCount = fake()->numberBetween(2, 6);
                    for ($m = 0; $m < $messageCount; $m++) {
                        /** @var User $sender */
                        $sender = fake()->boolean() ? $guest : User::findOrFail($property->host_id);
                        Message::factory()
                            ->when($m < $messageCount - 1, fn ($f) => $f->read())
                            ->create([
                                'conversation_id' => $conversation->id,
                                'sender_id' => $sender->id,
                            ]);
                    }
                }
            }
        }

        foreach ($guests->take(5) as $guest) {
            $wishlistProperties = $allProperties->random(fake()->numberBetween(1, 3));
            foreach ($wishlistProperties as $property) {
                Wishlist::factory()->create([
                    'user_id' => $guest->id,
                    'property_id' => $property->id,
                ]);
            }
        }
    }
}

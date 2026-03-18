<?php

declare(strict_types=1);

namespace Modules\Announcements\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Announcements\Enums\AnnouncementLevel;
use Modules\Announcements\Enums\AnnouncementScope;
use Modules\Announcements\Models\Announcement;

/**
 * @extends Factory<Announcement>
 */
final class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'level' => fake()->randomElement(AnnouncementLevel::cases()),
            'scope' => AnnouncementScope::Global,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
        ];
    }
}

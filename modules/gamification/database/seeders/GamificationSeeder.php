<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use LevelUp\Experience\Models\Achievement;
use LevelUp\Experience\Models\Level;

final class GamificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLevels();
        $this->seedAchievements();
    }

    private function seedLevels(): void
    {
        if (Level::query()->exists()) {
            return;
        }

        $levels = [['level' => 1, 'next_level_experience' => null]];
        $cumulative = 100;

        for ($l = 2; $l <= 100; $l++) {
            $levels[] = ['level' => $l, 'next_level_experience' => $cumulative];
            $cumulative += 10 * $l;
        }

        Level::add(...$levels);
    }

    private function seedAchievements(): void
    {
        $achievements = [
            [
                'name' => 'Profile Completed',
                'description' => 'Complete your profile during onboarding',
                'is_secret' => false,
                'image' => null,
            ],
        ];

        foreach ($achievements as $attrs) {
            Achievement::query()->firstOrCreate(['name' => $attrs['name']], [
                'description' => $attrs['description'],
                'is_secret' => $attrs['is_secret'],
                'image' => $attrs['image'],
            ]);
        }
    }
}

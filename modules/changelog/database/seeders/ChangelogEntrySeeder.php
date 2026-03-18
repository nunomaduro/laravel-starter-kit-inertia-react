<?php

declare(strict_types=1);

namespace Modules\Changelog\Database\Seeders;

use Database\Seeders\Concerns\LoadsJsonData;
use Illuminate\Database\Seeder;
use Modules\Changelog\Enums\ChangelogType;
use Modules\Changelog\Models\ChangelogEntry;
use RuntimeException;

final class ChangelogEntrySeeder extends Seeder
{
    use LoadsJsonData;

    private const int MIN_ENTRIES = 3;

    public function run(): void
    {
        try {
            $data = $this->loadJson('changelog-entries.json');
        } catch (RuntimeException) {
            $this->command?->warn('Changelog entries JSON file not found');
            $this->ensureMinimumEntries();

            return;
        }

        $entries = $data['changelog_entries'] ?? [];

        foreach ($entries as $entry) {
            if (ChangelogEntry::query()->where('title', $entry['title'])->where('version', $entry['version'] ?? null)->exists()) {
                continue;
            }

            ChangelogEntry::query()->create([
                'title' => $entry['title'],
                'description' => $entry['description'],
                'version' => $entry['version'] ?? null,
                'type' => ChangelogType::from($entry['type']),
                'is_published' => $entry['is_published'] ?? false,
                'released_at' => $entry['released_at'] ?? null,
            ]);
        }

        $this->ensureMinimumEntries();
        $this->command?->info('Changelog entries seeded.');
    }

    private function ensureMinimumEntries(): void
    {
        $current = ChangelogEntry::query()->count();
        if ($current >= self::MIN_ENTRIES) {
            return;
        }

        ChangelogEntry::factory()->count(self::MIN_ENTRIES - $current)->create();
    }
}

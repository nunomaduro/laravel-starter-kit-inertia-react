<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Enums\ChangelogType;
use App\Models\ChangelogEntry;
use Database\Seeders\Concerns\LoadsJsonData;
use Illuminate\Database\Seeder;
use RuntimeException;

final class ChangelogEntrySeeder extends Seeder
{
    use LoadsJsonData;

    public function run(): void
    {
        try {
            $data = $this->loadJson('changelog-entries.json');
        } catch (RuntimeException) {
            $this->command?->warn('Changelog entries JSON file not found');

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

        $this->command?->info('Changelog entries seeded.');
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Database\Seeder;

final class PageRevisionSeeder extends Seeder
{
    /** @var list<string> */
    private array $dependencies = ['PageSeeder'];

    public function run(): void
    {
        $pages = Page::query()->withoutGlobalScopes()->limit(5)->get();

        if ($pages->isEmpty()) {
            return;
        }

        foreach ($pages as $page) {
            $revisionCount = fake()->numberBetween(1, 2);
            for ($i = 0; $i < $revisionCount; $i++) {
                PageRevision::factory()->create([
                    'page_id' => $page->id,
                    'puck_json' => $page->puck_json ?? ['root' => (object) [], 'content' => []],
                    'name' => $page->name.' (revision '.($i + 1).')',
                    'slug' => $page->slug.'-rev-'.($i + 1),
                ]);
            }
        }
    }
}

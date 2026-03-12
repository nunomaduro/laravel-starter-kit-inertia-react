<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Category;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds (idempotent). Rich set for blog, help, and filters.
     */
    public function run(): void
    {
        if (Category::query()->exists()) {
            return;
        }

        Category::create(['name' => 'Default', 'slug' => 'default', 'type' => 'default']);
        Category::create(['name' => 'Development', 'slug' => 'development', 'type' => 'default']);
        Category::create(['name' => 'Support', 'slug' => 'support', 'type' => 'default']);
        Category::create(['name' => 'Product updates', 'slug' => 'product-updates', 'type' => 'default']);
        Category::create(['name' => 'Announcements', 'slug' => 'announcements', 'type' => 'default']);

        $defaultRoot = Category::query()->where('slug', 'default')->first();
        if ($defaultRoot instanceof Category) {
            $child = new Category([
                'name' => 'General',
                'slug' => 'general',
                'type' => 'default',
            ]);
            $child->appendToNode($defaultRoot)->save();
        }
    }
}

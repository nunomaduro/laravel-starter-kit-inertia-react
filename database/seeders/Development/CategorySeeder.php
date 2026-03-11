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

        $default = Category::create(['name' => 'Default', 'type' => 'default']);
        Category::create(['name' => 'Development', 'type' => 'default']);
        Category::create(['name' => 'Support', 'type' => 'default']);
        Category::create(['name' => 'Product updates', 'type' => 'default']);
        Category::create(['name' => 'Announcements', 'type' => 'default']);
        $sub = new Category(['name' => 'Subcategory', 'type' => 'default']);
        $default->appendNode($sub);
    }
}

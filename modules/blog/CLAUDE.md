# Blog Module

Blog posts with categories, tags, and SEO support.

## Purpose

Provides a public-facing blog with posts, category filtering, and admin management via Filament. Categories are shared from core (`App\Models\Category`). Includes a DataTable for post management.

## Structure

```
modules/blog/
├── module.json
├── database/
│   ├── factories/PostFactory.php
│   └── seeders/PostSeeder.php
├── routes/
│   └── web.php
└── src/
    ├── BlogServiceProvider.php
    ├── DataTables/PostDataTable.php
    ├── Features/BlogFeature.php
    ├── Filament/Resources/Posts/
    │   ├── PostResource.php
    │   ├── Pages/                       # Create, Edit, List, View pages
    │   ├── Schemas/                     # Form and infolist schemas
    │   └── Tables/PostsTable.php
    ├── Http/Controllers/
    │   ├── BlogController.php
    │   └── PostsTableController.php
    └── Models/Post.php
```

## Key Classes

- **Model**: `Modules\Blog\Models\Post`
- **Feature**: `Modules\Blog\Features\BlogFeature`
- **Provider**: `Modules\Blog\BlogServiceProvider`

## Dependencies

- `App\Models\Category` (core) — shared categorization system

## Toggle

Enable/disable via `config/modules.php` or `php artisan module:enable blog` / `module:disable blog`.

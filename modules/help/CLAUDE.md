# Help Module

Help center with articles, categories, and article ratings.

## Purpose

Provides a public help center with searchable articles organized by categories. Users can rate articles. Admins manage articles via Filament.

## Structure

```
modules/help/
├── module.json
├── database/
│   ├── factories/HelpArticleFactory.php
│   └── seeders/HelpArticleSeeder.php
├── routes/
│   └── web.php
└── src/
    ├── HelpServiceProvider.php
    ├── Actions/RateHelpArticleAction.php
    ├── Features/HelpFeature.php
    ├── Filament/Resources/HelpArticles/
    │   ├── HelpArticleResource.php
    │   ├── Pages/                       # Create, Edit, List, View pages
    │   ├── Schemas/                     # Form and infolist schemas
    │   └── Tables/HelpArticlesTable.php
    ├── Http/Controllers/
    │   ├── HelpCenterController.php
    │   └── RateHelpArticleController.php
    └── Models/HelpArticle.php
```

## Key Classes

- **Model**: `Modules\Help\Models\HelpArticle`
- **Feature**: `Modules\Help\Features\HelpFeature`
- **Provider**: `Modules\Help\HelpServiceProvider`

## Dependencies

- `App\Models\Concerns\Categorizable` (core trait) — shared categorization system

## Toggle

Enable/disable via `config/modules.php` or `php artisan module:enable help` / `module:disable help`.

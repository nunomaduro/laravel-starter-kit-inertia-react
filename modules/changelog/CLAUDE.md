# Changelog Module

Changelog entries and release notes management.

## Purpose

Provides a public changelog page showing versioned release notes. Admins create and manage entries via Filament.

## Structure

```
modules/changelog/
├── module.json
├── database/
│   ├── factories/ChangelogEntryFactory.php
│   └── seeders/ChangelogEntrySeeder.php
├── routes/
│   └── web.php
└── src/
    ├── ChangelogServiceProvider.php
    ├── Enums/ChangelogType.php          # Entry type classification
    ├── Features/ChangelogFeature.php
    ├── Filament/Resources/ChangelogEntries/
    │   ├── ChangelogEntryResource.php
    │   ├── Pages/                       # Create, Edit, List, View pages
    │   ├── Schemas/                     # Form and infolist schemas
    │   └── Tables/ChangelogEntriesTable.php
    ├── Http/Controllers/ChangelogController.php
    └── Models/ChangelogEntry.php
```

## Key Classes

- **Model**: `Modules\Changelog\Models\ChangelogEntry`
- **Feature**: `Modules\Changelog\Features\ChangelogFeature`
- **Provider**: `Modules\Changelog\ChangelogServiceProvider`

## Toggle

Enable/disable via `config/modules.php` or `php artisan module:enable changelog` / `module:disable changelog`.

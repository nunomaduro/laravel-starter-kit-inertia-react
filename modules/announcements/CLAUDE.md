# Announcements Module

In-app announcement banners with audience targeting, scheduling, and DataTable management.

## Purpose

Lets super-admins and org admins display dismissible banners to users. Announcements can be global (all users) or organization-scoped (only members of a given org). Includes a DataTable for admin management.

## Structure

```
modules/announcements/
├── module.json
├── database/
│   ├── factories/AnnouncementFactory.php
│   └── seeders/AnnouncementSeeder.php
├── routes/
│   └── web.php
└── src/
    ├── AnnouncementsServiceProvider.php
    ├── DataTables/AnnouncementDataTable.php
    ├── Enums/
    │   ├── AnnouncementLevel.php        # Info, Warning, Maintenance
    │   └── AnnouncementScope.php        # Global, Organization
    ├── Features/AnnouncementsFeature.php
    ├── Filament/Resources/Announcements/
    │   ├── AnnouncementResource.php
    │   ├── Pages/                       # Create, Edit, List pages
    │   ├── Schemas/AnnouncementForm.php
    │   └── Tables/
    ├── Http/Controllers/AnnouncementsTableController.php
    ├── Models/Announcement.php
    └── Policies/AnnouncementPolicy.php
```

## Key Classes

- **Model**: `Modules\Announcements\Models\Announcement`
- **Feature**: `Modules\Announcements\Features\AnnouncementsFeature`
- **Provider**: `Modules\Announcements\AnnouncementsServiceProvider`
- **Enums**: `AnnouncementLevel`, `AnnouncementScope`

## Toggle

Enable/disable via `config/modules.php` or `php artisan module:enable announcements` / `module:disable announcements`.

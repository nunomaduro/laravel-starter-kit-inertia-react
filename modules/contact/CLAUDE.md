# Contact Module

Contact form and submission management.

## Purpose

Provides a public contact form and admin submission management. Users can submit contact requests; admins review and manage them via Filament.

## Structure

```
modules/contact/
├── module.json                          # Module metadata and seeder list
├── database/
│   ├── factories/ContactSubmissionFactory.php
│   └── seeders/ContactSubmissionSeeder.php
├── routes/
│   └── web.php                          # Contact form routes
└── src/
    ├── ContactServiceProvider.php       # Module service provider
    ├── Actions/
    │   └── StoreContactSubmission.php   # Handles storing submissions
    ├── Features/
    │   └── ContactFeature.php           # Pennant feature flag
    ├── Filament/Resources/ContactSubmissions/
    │   ├── ContactSubmissionResource.php
    │   ├── Pages/                       # List, Edit, View pages
    │   ├── Schemas/                     # Form and infolist schemas
    │   └── Tables/                      # Table configuration
    ├── Http/
    │   ├── Controllers/ContactSubmissionController.php
    │   └── Requests/StoreContactSubmissionRequest.php
    └── Models/
        └── ContactSubmission.php
```

## Key Classes

- **Model**: `Modules\Contact\Models\ContactSubmission`
- **Feature**: `Modules\Contact\Features\ContactFeature`
- **Provider**: `Modules\Contact\ContactServiceProvider`

## Toggle

Enable/disable via `config/modules.php` or `php artisan module:enable contact` / `module:disable contact`.

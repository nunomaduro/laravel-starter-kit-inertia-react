<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\DataTableServiceProvider::class,
    App\Providers\OnboardingServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\SystemPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\HealthServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\MemoryServiceProvider::class,
    App\Providers\PanServiceProvider::class,
    App\Providers\PermissionServiceProvider::class,
    App\Providers\SettingsOverlayServiceProvider::class,
    // Modules
    Modules\Hr\Providers\HrModuleServiceProvider::class,
    Modules\Crm\Providers\CrmModuleServiceProvider::class,
];

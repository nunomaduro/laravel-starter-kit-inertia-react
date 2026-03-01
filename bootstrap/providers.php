<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FakerServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\MemoryServiceProvider::class,
    App\Providers\PermissionServiceProvider::class,
    App\Providers\SettingsOverlayServiceProvider::class,
    App\Providers\WaterlineServiceProvider::class,
];

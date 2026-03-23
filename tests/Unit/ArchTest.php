<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security()->ignoring([
    'assert',
    App\Console\Commands\AppDemoCommand::class,
    App\Console\Commands\ModuleInstallCommand::class,
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed()
    ->ignoring([
        App\Http\Controllers\Api\V1\BaseApiController::class,
        App\Http\Controllers\Controller::class, // base class extended by all controllers
    ]);

// Prism: only PrismService may use the Prism facade; all other app code must use PrismService or ai().
arch('Prism facade only in PrismService')
    ->expect('App')
    ->not->toUse(Prism\Prism\Facades\Prism::class)
    ->ignoring(App\Services\PrismService::class);

// Relay: only PrismService and PrismValidate may use the Relay facade (PrismService for MCP tools, PrismValidate to validate config).
arch('Relay facade only in PrismService and PrismValidate')
    ->expect('App')
    ->not->toUse(Prism\Relay\Facades\Relay::class)
    ->ignoring([App\Services\PrismService::class, App\Console\Commands\PrismValidate::class]);

// Filament: main app (Controllers, Actions) must not use Filament; admin panel is separate from Inertia.
arch('Filament is not used by main app')
    ->expect(['App\Http\Controllers', 'App\Actions'])
    ->not->toUse('App\Filament');

// Filament: admin layer must not use main app HTTP or action layer.
arch('Filament does not use Controllers or Actions')
    ->expect('App\Filament')
    ->not->toUse(['App\Http\Controllers', 'App\Actions']);

// Seeding: seeders in categories must extend base Seeder.
arch('seeders in Essential extend base seeder')
    ->expect('Database\Seeders\Essential')
    ->toExtend(Illuminate\Database\Seeder::class);

arch('seeders in Development extend base seeder')
    ->expect('Database\Seeders\Development')
    ->toExtend(Illuminate\Database\Seeder::class);

arch('seeders in Production extend base seeder')
    ->expect('Database\Seeders\Production')
    ->toExtend(Illuminate\Database\Seeder::class);

// Seeding: category seeders must not use Controllers or HTTP layer.
arch('seeders do not use Controllers')
    ->expect(['Database\Seeders\Essential', 'Database\Seeders\Development', 'Database\Seeders\Production'])
    ->not->toUse('App\Http\Controllers');

// Seeding: category seeders may only use allowed layers (Models, Seeders, Illuminate, Spatie).
arch('seeders only use allowed layers')
    ->expect(['Database\Seeders\Essential', 'Database\Seeders\Development', 'Database\Seeders\Production'])
    ->toOnlyUse([
        'App\Enums',
        'App\Events',
        'App\Models',
        'App\Notifications',
        'App\Services',
        'App\Support',
        'Database\Seeders',
        'Database\Factories',
        'Illuminate\Database',
        'Illuminate\Support',
        'Illuminate\Contracts',
        'Illuminate\Foundation',
        'LevelUp\Experience',
        'MartinPetricko\LaravelDatabaseMail',
        'Modules\Billing',
        'Modules\PageBuilder',
        'Modules\Workflows',
        'Pgvector\Laravel',
        'Spatie\Permission',
    ])
    ->ignoring(['RuntimeException', 'Throwable', 'app', 'config', 'database_path', 'fake', 'now', 'resolve']);

// Strict preset disabled: Filament resource pages override protected getHeaderActions()
// and LoadsJsonData uses protected loadJson(); strict()->ignoring() did not exclude them.
// arch()->preset()->strict();

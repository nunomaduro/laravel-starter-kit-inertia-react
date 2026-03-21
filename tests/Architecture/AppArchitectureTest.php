<?php

declare(strict_types=1);

// Fortify actions implement contract interfaces (update(), create(), etc.) not handle().
// All other app actions must have a handle() method.
arch('Actions follow single responsibility pattern')
    ->expect('App\Actions')
    ->toHaveMethod('handle')
    ->ignoring([
        'App\Actions\Concerns',
        'App\Actions\Fortify',
    ]);

// Fortify actions implement contracts and use traits — exclude them.
arch('Actions are final classes')
    ->expect('App\Actions')
    ->toBeFinal()
    ->ignoring([
        'App\Actions\Concerns',
        'App\Actions\Fortify\PasswordValidationRules', // trait, not a class
    ]);

arch('Models do not call Actions directly')
    ->expect('App\Models')
    ->not->toUse('App\Actions');

// Fortify action contracts receive Request — exclude that namespace.
arch('Actions do not use HTTP request/response objects')
    ->expect('App\Actions')
    ->not->toUse([
        'Illuminate\Http\Request',
        'Illuminate\Http\Response',
        'Illuminate\Http\JsonResponse',
        'Illuminate\Http\RedirectResponse',
    ])
    ->ignoring('App\Actions\Fortify');

arch('Filament Resources use correct action namespace')
    ->expect('App\Filament')
    ->not->toUse('Filament\Tables\Actions\Action')
    ->not->toUse('Filament\Forms\Actions\Action');

arch('Settings classes live in App\Settings')
    ->expect('App\Settings')
    ->toExtend('Spatie\LaravelSettings\Settings')
    ->toBeFinal();

arch('DataTables live in App\DataTables')
    ->expect('App\DataTables')
    ->toExtend('Machour\DataTable\AbstractDataTable')
    ->toBeFinal();

arch('Providers do not perform heavy operations in register()')
    ->expect('App\Providers')
    ->not->toUse('Illuminate\Support\Facades\DB')
    ->ignoring('App\Providers\AppServiceProvider');

arch('Jobs implement ShouldQueue')
    ->expect('App\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue')
    ->ignoring('App\Jobs\Middleware');

arch('Enums use backed type')
    ->expect('App\Enums')
    ->toBeEnums();

arch('PHP strict types declared everywhere')
    ->expect('App')
    ->toUseStrictTypes();

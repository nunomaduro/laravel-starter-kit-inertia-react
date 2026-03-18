<?php

declare(strict_types=1);

namespace Modules\Contact;

use App\Features\ContactFeature;
use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Stub service provider for validating the module loading pipeline.
 *
 * Registers a single /module-test route to confirm the infrastructure
 * correctly loads and unloads module providers based on config/modules.php.
 */
final class ContactServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'contact';
    }

    public function featureKey(): string
    {
        return 'contact';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return ContactFeature::class;
    }

    protected function bootModule(): void
    {
        Route::get('/module-test', fn () => response()->json(['module' => 'contact', 'status' => 'ok']));
    }
}

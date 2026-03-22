<?php

declare(strict_types=1);

namespace Modules\Workflows;

use App\Models\User;
use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Workflows\Features\WorkflowsFeature;

final class WorkflowsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'workflows';
    }

    public function featureKey(): string
    {
        return 'workflows';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return WorkflowsFeature::class;
    }

    protected function bootModule(): void
    {
        $this->registerWaterlineGate();
    }

    /**
     * Register the Waterline gate. Only users who can access the Filament admin panel may view Waterline.
     */
    private function registerWaterlineGate(): void
    {
        Gate::define('viewWaterline', fn (?User $user = null): bool => $user instanceof User && $user->can('access admin panel'));
    }
}

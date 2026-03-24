<?php

declare(strict_types=1);

namespace Modules\Workflows\Providers;

use App\Models\User;
use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Workflows\Features\WorkflowsFeature;

final class WorkflowsModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'workflows',
            version: '1.0.0',
            description: 'Durable workflows with Waterline monitoring dashboard.',
            navigation: [
                ['label' => 'Workflows', 'route' => 'workflows.index', 'icon' => 'git-branch', 'group' => 'Platform'],
            ],
        );
    }

    protected function featureClass(): ?string
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

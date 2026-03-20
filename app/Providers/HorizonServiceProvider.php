<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

final class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Register the Horizon gate. Only users who can access the Filament admin panel may view Horizon.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', fn (?User $user = null): bool => $user instanceof User && $user->can('access admin panel'));
    }
}

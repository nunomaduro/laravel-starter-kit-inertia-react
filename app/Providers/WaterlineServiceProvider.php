<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Waterline\WaterlineApplicationServiceProvider;

final class WaterlineServiceProvider extends WaterlineApplicationServiceProvider
{
    /**
     * Register the Waterline gate. Only users who can access the Filament admin panel may view Waterline.
     */
    protected function gate(): void
    {
        Gate::define('viewWaterline', fn (?User $user = null): bool => $user instanceof User && $user->can('access admin panel'));
    }
}

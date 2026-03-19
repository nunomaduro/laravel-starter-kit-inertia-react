<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Spatie\Onboard\Facades\Onboard;

final class OnboardingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Onboard::addStep('Verify email', User::class)
            ->link('/verify-email')
            ->cta('Verify email')
            ->completeIf(function (User $model): bool {
                return $model->email_verified_at !== null;
            });

        Onboard::addStep('Complete profile', User::class)
            ->link('/settings/profile')
            ->cta('Complete profile')
            ->completeIf(function (User $model): bool {
                return (bool) $model->name && $model->getFirstMediaUrl('avatar', 'profile') !== '';
            });

        Onboard::addStep('Get started', User::class)
            ->link('/onboarding')
            ->cta('Get started')
            ->completeIf(function (User $model): bool {
                return $model->onboarding_completed;
            });
    }
}

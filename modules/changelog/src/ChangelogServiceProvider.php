<?php

declare(strict_types=1);

namespace Modules\Changelog;

use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Changelog\Features\ChangelogFeature;
use Modules\Changelog\Models\ChangelogEntry;
use Modules\Changelog\Policies\ChangelogEntryPolicy;

final class ChangelogServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'changelog';
    }

    public function featureKey(): string
    {
        return 'changelog';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return ChangelogFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(ChangelogEntry::class, ChangelogEntryPolicy::class);
    }
}

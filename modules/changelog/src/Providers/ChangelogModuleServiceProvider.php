<?php

declare(strict_types=1);

namespace Modules\Changelog\Providers;

use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Changelog\Features\ChangelogFeature;
use Modules\Changelog\Models\ChangelogEntry;
use Modules\Changelog\Policies\ChangelogEntryPolicy;

final class ChangelogModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'changelog',
            version: '1.0.0',
            description: 'Changelog entries and release notes management.',
            models: [ChangelogEntry::class],
            navigation: [
                ['label' => 'Changelog', 'route' => 'changelog.index', 'icon' => 'clock', 'group' => 'Content'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return ChangelogFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(ChangelogEntry::class, ChangelogEntryPolicy::class);
    }
}

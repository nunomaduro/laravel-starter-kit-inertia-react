<?php

declare(strict_types=1);

namespace Modules\Help\Providers;

use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Help\Features\HelpFeature;
use Modules\Help\Models\HelpArticle;
use Modules\Help\Policies\HelpArticlePolicy;

final class HelpModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'help',
            version: '1.0.0',
            description: 'Help center with articles, categories, and article ratings.',
            models: [HelpArticle::class],
            navigation: [
                ['label' => 'Help Center', 'route' => 'help.index', 'icon' => 'life-buoy', 'group' => 'Support'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return HelpFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(HelpArticle::class, HelpArticlePolicy::class);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Help;

use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Help\Features\HelpFeature;
use Modules\Help\Models\HelpArticle;
use Modules\Help\Policies\HelpArticlePolicy;

final class HelpServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'help';
    }

    public function featureKey(): string
    {
        return 'help';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return HelpFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(HelpArticle::class, HelpArticlePolicy::class);
    }
}

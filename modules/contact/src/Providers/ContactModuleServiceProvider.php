<?php

declare(strict_types=1);

namespace Modules\Contact\Providers;

use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Contact\Features\ContactFeature;
use Modules\Contact\Models\ContactSubmission;
use Modules\Contact\Policies\ContactSubmissionPolicy;

final class ContactModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'contact',
            version: '1.0.0',
            description: 'Contact form and submission management.',
            models: [ContactSubmission::class],
            navigation: [
                ['label' => 'Contact', 'route' => 'contact.create', 'icon' => 'mail', 'group' => 'Support'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return ContactFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(ContactSubmission::class, ContactSubmissionPolicy::class);
    }
}

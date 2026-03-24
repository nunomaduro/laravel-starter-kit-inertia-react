<?php

declare(strict_types=1);

namespace Modules\Contact;

use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Contact\Features\ContactFeature;
use Modules\Contact\Models\ContactSubmission;
use Modules\Contact\Policies\ContactSubmissionPolicy;

final class ContactServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'contact';
    }

    public function featureKey(): string
    {
        return 'contact';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return ContactFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(ContactSubmission::class, ContactSubmissionPolicy::class);
    }
}

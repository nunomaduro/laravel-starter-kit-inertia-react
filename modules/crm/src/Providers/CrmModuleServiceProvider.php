<?php

declare(strict_types=1);

namespace Modules\Crm\Providers;

use App\Modules\Contracts\DeclaresModuleRelationships;
use App\Modules\Contracts\ProvidesAIContext;
use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use App\Modules\Support\ModuleRelationship;
use Illuminate\Support\Facades\Gate;
use Modules\Crm\Models\Activity;
use Modules\Crm\Models\Contact;
use Modules\Crm\Models\Deal;
use Modules\Crm\Models\Pipeline;
use Modules\Crm\Policies\ActivityPolicy;
use Modules\Crm\Policies\ContactPolicy;
use Modules\Crm\Policies\DealPolicy;
use Modules\Crm\Policies\PipelinePolicy;

final class CrmModuleServiceProvider extends ModuleProvider implements DeclaresModuleRelationships, ProvidesAIContext
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'CRM',
            version: '1.0.0',
            description: 'Customer Relationship Management: contacts, deals, pipelines, activities',
            models: [
                Contact::class,
                Deal::class,
                Pipeline::class,
                Activity::class,
            ],
            pages: [
                'crm.contacts.index' => 'crm/contacts/index',
                'crm.deals.index' => 'crm/deals/index',
            ],
            navigation: [
                ['label' => 'Contacts', 'route' => 'crm.contacts.index', 'icon' => 'users'],
                ['label' => 'Deals', 'route' => 'crm.deals.index', 'icon' => 'dollar-sign'],
                ['label' => 'Pipelines', 'route' => 'crm.pipelines.index', 'icon' => 'git-branch'],
            ],
        );
    }

    /**
     * Cross-module relationship: CRM contacts can be assigned to HR employees.
     *
     * If the HR module is not installed, this relationship is gracefully skipped.
     */
    public function relationships(): array
    {
        return [
            new ModuleRelationship(
                sourceModel: 'crm::contact',
                targetModel: 'hr::employee',
                type: 'belongsTo',
                foreignKey: 'assigned_employee_id',
            ),
        ];
    }

    public function systemPrompt(): string
    {
        return <<<'PROMPT'
        ## CRM Module
        This application manages customer relationships:
        - **Contacts**: People or leads with name, email, phone, company, position, source, and status (lead, prospect, customer, churned)
        - **Deals**: Sales opportunities tied to a contact with value, currency, stage, probability, and expected close date
        - **Pipelines**: Sales pipeline definitions with named stages (e.g., Lead → Qualified → Proposal → Won/Lost)
        - **Activities**: Interactions logged against contacts or deals — types: call, email, meeting, note, task

        Key relationships: Deals belong to Contacts and Pipelines. Activities belong to Contacts and/or Deals.
        If the HR module is installed, Contacts can be assigned to Employees for account management.
        All data is scoped to the current organization (multi-tenant).
        PROMPT;
    }

    public function tools(): array
    {
        return [];
    }

    public function searchableModels(): array
    {
        return [
            Contact::class,
        ];
    }

    protected function bootModule(): void
    {
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Deal::class, DealPolicy::class);
        Gate::policy(Pipeline::class, PipelinePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
    }
}

<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('tenancy.term', config('tenancy.term', 'Organization'));
        $this->migrator->add('tenancy.term_plural', config('tenancy.term_plural', 'Organizations'));
        $this->migrator->add('tenancy.allow_user_org_creation', (bool) config('tenancy.allow_user_organization_creation', true));
        $this->migrator->add('tenancy.default_org_name', config('tenancy.default_organization_name', "{name}'s Workspace"));
        $this->migrator->add('tenancy.auto_create_personal_org', (bool) config('tenancy.auto_create_personal_organization', true));
        $this->migrator->add('tenancy.invitation_expires_in_days', (int) config('tenancy.invitations.expires_in_days', 7));
        $this->migrator->add('tenancy.invitation_allow_registration', (bool) config('tenancy.invitations.allow_registration', true));
        $this->migrator->add('tenancy.sharing_restrict_to_connected', (bool) config('tenancy.sharing.restrict_to_connected', false));
        $this->migrator->add('tenancy.sharing_edit_ownership', config('tenancy.sharing.edit_ownership', 'original_owner'));
        $this->migrator->add('tenancy.super_admin_can_view_all', (bool) config('tenancy.super_admin.can_view_all', true));
    }
};

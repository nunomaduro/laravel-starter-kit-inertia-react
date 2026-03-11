<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add(
            'tenancy.auto_create_personal_org_for_admins',
            (bool) config('tenancy.auto_create_personal_organization_for_admins', true)
        );
        $this->migrator->add(
            'tenancy.auto_create_personal_org_for_members',
            (bool) config('tenancy.auto_create_personal_organization_for_members', false)
        );
        $this->migrator->add(
            'tenancy.super_admin_default_share_new_to_all_orgs',
            (bool) config('tenancy.super_admin.default_share_new_to_all_orgs', true)
        );
    }
};

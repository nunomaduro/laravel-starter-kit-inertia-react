<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class TenancySettings extends Settings
{
    public bool $enabled = true;

    public ?string $domain = null;

    public bool $subdomain_resolution = true;

    public string $term = 'Organization';

    public string $term_plural = 'Organizations';

    public bool $allow_user_org_creation = true;

    public string $default_org_name = "{name}'s Workspace";

    public bool $auto_create_personal_org = true;

    public bool $auto_create_personal_org_for_admins = true;

    public bool $auto_create_personal_org_for_members = false;

    public int $invitation_expires_in_days = 7;

    public bool $invitation_allow_registration = true;

    public bool $sharing_restrict_to_connected = false;

    public string $sharing_edit_ownership = 'original_owner';

    public bool $super_admin_can_view_all = true;

    public bool $super_admin_default_share_new_to_all_orgs = true;

    public static function group(): string
    {
        return 'tenancy';
    }
}

<?php

declare(strict_types=1);

// Managed via Filament: Settings > Tenancy (DB-backed via SettingsOverlayServiceProvider)
return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Organization Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, the application operates in multi-organization (tenant)
    | mode. Users can belong to multiple organizations and switch between them.
    | When disabled, the application operates in single-tenant mode.
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Tenant Term
    |--------------------------------------------------------------------------
    |
    | The user-facing term used for organizations. This is used in UI text
    | and error messages. Common options: 'organization', 'team', 'workspace',
    | 'company', 'account'.
    |
    */
    'term' => 'Organization',

    /*
    |--------------------------------------------------------------------------
    | Tenant Term (Plural)
    |--------------------------------------------------------------------------
    |
    | The plural form of the tenant term used in UI text.
    |
    */
    'term_plural' => 'Organizations',

    /*
    |--------------------------------------------------------------------------
    | Allow User Organization Creation
    |--------------------------------------------------------------------------
    |
    | When enabled, users can create new organizations. When disabled,
    | only admins can create organizations for users.
    |
    */
    'allow_user_organization_creation' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Organization Name
    |--------------------------------------------------------------------------
    |
    | The default name for the personal organization created when a user
    | registers. Use {name} as a placeholder for the user's name.
    |
    */
    'default_organization_name' => "{name}'s Workspace",

    /*
    |--------------------------------------------------------------------------
    | Auto-Create Personal Organization
    |--------------------------------------------------------------------------
    |
    | When enabled, a personal organization is automatically created for
    | each new user during registration. Superseded by the for_admins and
    | for_members variants when set (e.g. via Settings or install).
    |
    */
    'auto_create_personal_organization' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-Create Personal Organization (for admins)
    |--------------------------------------------------------------------------
    |
    | When enabled, a personal organization is created for users who register
    | or are created as org admins (e.g. self-registration, invited as admin).
    |
    */
    'auto_create_personal_organization_for_admins' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-Create Personal Organization (for members)
    |--------------------------------------------------------------------------
    |
    | When enabled, a personal organization is created for users who join
    | only as members (e.g. invited as member and register to accept).
    |
    */
    'auto_create_personal_organization_for_members' => false,

    /*
    |--------------------------------------------------------------------------
    | Domain & Subdomain Resolution
    |--------------------------------------------------------------------------
    |
    | Base domain for subdomain-based tenant resolution. When host is
    | {slug}.{domain} (e.g. acme.example.com), the organization with that slug
    | is set as current tenant. Set to null to disable subdomain resolution.
    |
    */
    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Subdomain Resolution
    |--------------------------------------------------------------------------
    |
    | When true, requests to {slug}.{tenancy.domain} resolve to the organization
    | with that slug. When false, only verified organization_domains are used.
    |
    */
    'subdomain_resolution' => true,

    /*
    |--------------------------------------------------------------------------
    | Invitation Settings
    |--------------------------------------------------------------------------
    */
    'invitations' => [
        'expires_in_days' => 7,
        'allow_registration' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sharing Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for cross-organization data sharing.
    |
    */
    'sharing' => [
        // Restrict sharing to only "connected" organizations
        'restrict_to_connected' => false,

        // What happens when shared data is edited:
        // 'original_owner' - edits belong to original org, shared org loses access on revocation
        // 'copy_on_edit' - any edit creates a copy for the editing org
        'edit_ownership' => 'original_owner',
    ],

    /*
    |--------------------------------------------------------------------------
    | Super-Admin Settings
    |--------------------------------------------------------------------------
    |
    | Settings for super-admin bypass and cross-organization access.
    |
    */
    'super_admin' => [
        // Allow super-admins to view all organizations' data
        'can_view_all' => true,

        // Session key for super-admin view-all mode
        'view_all_session_key' => 'view_all_organizations',

        // When true, create/edit forms for shareable (HasVisibility) models default
        // "Share to all organizations" to on for super-admins
        'default_share_new_to_all_orgs' => true,
    ],

    /*
    | Set to true only while DatabaseSeeder is running (suppresses UserCreated
    | personal-org creation so Spatie assignRole is never hit during seed).
    */
    'seed_in_progress' => false,

];

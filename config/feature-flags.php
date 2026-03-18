<?php

declare(strict_types=1);

use App\Features\ApiAccessFeature;
use App\Features\AppearanceSettingsFeature;
use App\Features\BlogFeature;
use App\Features\ChangelogFeature;
use App\Features\ComponentShowcaseFeature;
use App\Features\CookieConsentFeature;
use App\Features\GamificationFeature;
use App\Features\HelpFeature;
use App\Features\ImpersonationFeature;
use App\Features\OnboardingFeature;
use App\Features\PersonalDataExportFeature;
use App\Features\ProfilePdfExportFeature;
use App\Features\RegistrationFeature;
use App\Features\ScrambleApiDocsFeature;
use App\Features\TwoFactorAuthFeature;
use Modules\Contact\Features\ContactFeature;

return [
    /*
     * Comma-separated feature keys that are globally disabled for all users (including super-admins).
     * Keys must match those in inertia_features/route_feature_map (e.g. blog, changelog, gamification).
     * When disabled, Pennant is not consulted; the feature is always off.
     */
    // Managed via Filament: Settings > Feature Flags
    'globally_disabled' => [],

    /*
     * Feature classes to resolve and expose to the Inertia frontend as shared props.
     * Keys become the feature name in the `features` object (e.g. BlogFeature -> blog).
     */
    'inertia_features' => [
        'blog' => BlogFeature::class,
        'changelog' => ChangelogFeature::class,
        'help' => HelpFeature::class,
        'contact' => ContactFeature::class,
        'cookie_consent' => CookieConsentFeature::class,
        'profile_pdf_export' => ProfilePdfExportFeature::class,
        'onboarding' => OnboardingFeature::class,
        'two_factor_auth' => TwoFactorAuthFeature::class,
        'impersonation' => ImpersonationFeature::class,
        'personal_data_export' => PersonalDataExportFeature::class,
        'registration' => RegistrationFeature::class,
        'api_access' => ApiAccessFeature::class,
        'scramble_api_docs' => ScrambleApiDocsFeature::class,
        'appearance_settings' => AppearanceSettingsFeature::class,
        'gamification' => GamificationFeature::class,
    ],

    /*
     * Per-feature metadata controlling org-level delegation and plan requirements.
     * delegate_to_orgs: true = org admin can toggle this feature org-wide
     * plan_required: null = all plans; 'pro'|'enterprise' = plan-gated
     */
    'feature_metadata' => [
        'blog' => ['delegate_to_orgs' => true,  'plan_required' => null],
        'changelog' => ['delegate_to_orgs' => true,  'plan_required' => null],
        'help' => ['delegate_to_orgs' => true,  'plan_required' => null],
        'contact' => ['delegate_to_orgs' => true,  'plan_required' => null],
        'onboarding' => ['delegate_to_orgs' => true,  'plan_required' => null],
        'appearance_settings' => ['delegate_to_orgs' => true,  'plan_required' => null],
        'gamification' => ['delegate_to_orgs' => true,  'plan_required' => 'pro'],
        'api_access' => ['delegate_to_orgs' => true,  'plan_required' => 'pro'],
        'two_factor_auth' => ['delegate_to_orgs' => false, 'plan_required' => null],
        'impersonation' => ['delegate_to_orgs' => false, 'plan_required' => null],
        'personal_data_export' => ['delegate_to_orgs' => false, 'plan_required' => null],
        'registration' => ['delegate_to_orgs' => false, 'plan_required' => null],
        'cookie_consent' => ['delegate_to_orgs' => false, 'plan_required' => null],
        'profile_pdf_export' => ['delegate_to_orgs' => false, 'plan_required' => null],
        'scramble_api_docs' => ['delegate_to_orgs' => false, 'plan_required' => null],
    ],

    /*
     * Map of route-middleware key to feature class for EnsureFeatureActive middleware.
     */
    'route_feature_map' => [
        'api_access' => ApiAccessFeature::class,
        'appearance_settings' => AppearanceSettingsFeature::class,
        'gamification' => GamificationFeature::class,
        'blog' => BlogFeature::class,
        'changelog' => ChangelogFeature::class,
        'contact' => ContactFeature::class,
        'cookie_consent' => CookieConsentFeature::class,
        'help' => HelpFeature::class,
        'onboarding' => OnboardingFeature::class,
        'personal_data_export' => PersonalDataExportFeature::class,
        'profile_pdf_export' => ProfilePdfExportFeature::class,
        'scramble_api_docs' => ScrambleApiDocsFeature::class,
        'two_factor_auth' => TwoFactorAuthFeature::class,
        'component_showcase' => ComponentShowcaseFeature::class,
    ],
];

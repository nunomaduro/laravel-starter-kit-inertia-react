import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface OrganizationSummary {
    id: number;
    name: string;
    slug: string;
}

export interface Auth {
    user: User;
    /** Permission names for the current user (empty when guest). Use with useCan() or <Can>. */
    permissions: string[];
    /** Role names for the current user (empty when guest). */
    roles: string[];
    /** True when user has bypass-permissions (e.g. super-admin). useCan() treats as allowed for any permission. */
    can_bypass: boolean;
    /** Whether multi-organization (tenant) mode is enabled. When false, org switcher and org management UI are hidden. */
    tenancy_enabled?: boolean;
    /** Current tenant organization (set when user has org context). */
    current_organization?: OrganizationSummary | null;
    /** Organizations the user belongs to (when tenancy enabled; empty when single-tenant). */
    organizations?: OrganizationSummary[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface ModuleNavItem {
    label: string;
    route: string;
    /** Lucide icon name in kebab-case, e.g. 'file-text', 'credit-card'. */
    icon: string;
    module: string;
    group: string;
    permission?: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
    /** Permission name(s) required to show this item (user must have any). Omit to show to all authenticated users. */
    permission?: string | string[];
    /** Feature flag key (e.g. 'blog'). Item is hidden when this feature is inactive. */
    feature?: string;
    /** When true, item is hidden in single-tenant mode (MULTI_ORGANIZATION_ENABLED=false). */
    tenancyRequired?: boolean;
    /** Pan product analytics name (letters, numbers, dashes, underscores). Must be whitelisted in AppServiceProvider. */
    dataPan?: string;
    /** When true, item is only visible to super-admin users. */
    superAdminOnly?: boolean;
    /** Sidebar group label for organizing nav items into sections. */
    group?: string;
}

/** Pennant feature flags shared to the frontend (key => active for current user/guest default). */
export interface SharedFeatures {
    api_access?: boolean;
    appearance_settings?: boolean;
    blog?: boolean;
    changelog?: boolean;
    contact?: boolean;
    cookie_consent?: boolean;
    gamification?: boolean;
    help?: boolean;
    impersonation?: boolean;
    onboarding?: boolean;
    personal_data_export?: boolean;
    profile_pdf_export?: boolean;
    registration?: boolean;
    scramble_api_docs?: boolean;
    two_factor_auth?: boolean;
    [key: string]: boolean | undefined;
}

export interface ThemeProps {
    preset?: string;
    base_color?: string;
    radius?: string;
    font?: string;
    default_appearance?: string;
    /** Tailux dark color scheme (e.g. 'navy', 'mirage', 'mint', 'black', 'cinder') */
    dark?: string;
    /** Tailux primary color (e.g. 'indigo', 'blue', 'green', 'amber', 'purple', 'rose') */
    primary?: string;
    /** Tailux light color scheme (e.g. 'slate', 'gray', 'neutral') */
    light?: string;
    /** Tailux card skin (e.g. 'shadow', 'bordered') */
    skin?: string;
    /** Sidebar layout variant ('main' | 'sideblock') */
    layout?: string;
    /** Menu color scheme ('default' | 'primary' | 'muted') */
    menuColor?: string;
    /** Menu accent style ('subtle' | 'strong' | 'bordered') */
    menuAccent?: string;
    /** True when the current user may customize their personal theme. */
    canCustomize?: boolean;
    /** System-wide: when true, users may change appearance unless org denies. When false, only org admins can. */
    allowUserThemeCustomization?: boolean;
    /** The authenticated user's personal dark/light/system mode preference. */
    userMode?: 'dark' | 'light' | 'system';
    /** True when the current user has org.settings.manage permission (can upload logo & trigger AI theme). */
    canManageBranding?: boolean;
    /** Setting keys locked by system admin (orgs cannot override these). */
    lockedSettings?: string[];
    /** Granular per-category customization permissions for the current user. */
    canCustomizeGranular?: {
        colors: boolean;
        font: boolean;
        layout: boolean;
        logo: boolean;
    };
}

export interface BrandingProps {
    logoUrl?: string | null;
    logoUrlDark?: string | null;
    themePreset?: string | null;
    themeRadius?: string | null;
    themeFont?: string | null;
    allowUserCustomization?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    /** Feature flags (guest = default value, authenticated = resolved for user). */
    features: SharedFeatures;
    sidebarOpen: boolean;
    /** App-wide theme (from ThemeSettings overlay). Org branding may override preset/radius/font. */
    theme?: ThemeProps;
    /** Org branding (logo, theme overrides). Resolved lazily after tenant context. */
    branding?: BrandingProps;
    /** Notification summary shared on every Inertia response. */
    notifications?: {
        unread_count: number;
    };
    /** Whether the setup wizard has been completed (super-admin flow). */
    setup_complete: boolean;
    /** Active site-wide and org announcements (authenticated users only). */
    announcements?: Array<{
        id: number;
        title: string;
        body: string;
        level: string;
    }>;
    /** Multi-step onboarding state (spatie/laravel-onboard); only when feature is active. */
    onboarding?: {
        steps: Array<{ title: string; cta: string; link: string; complete: boolean }>;
        inProgress: boolean;
        percentageCompleted: number;
        nextStep: { title: string; link: string; cta: string } | null;
    };
    /** Module nav items grouped by section, driven by ModuleNavigationRegistry. */
    moduleNavItems: Record<string, ModuleNavItem[]>;
    [key: string]: unknown;
}

export interface AppNotification {
    id: string;
    type: string;
    data: {
        title: string;
        message: string;
        type: 'info' | 'success' | 'warning' | 'error';
        action_url?: string | null;
    };
    read_at: string | null;
    created_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    avatar?: string | null;
    avatar_profile?: string | null;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface OrgDomain {
    id: number;
    domain: string;
    type: 'subdomain' | 'custom';
    status:
        | 'pending_dns'
        | 'dns_verified'
        | 'ssl_provisioning'
        | 'active'
        | 'error'
        | 'expired';
    is_verified: boolean;
    is_primary: boolean;
    cname_target: string | null;
    failure_reason: string | null;
    dns_check_attempts: number;
    ssl_expires_at: string | null;
    verified_at: string | null;
}

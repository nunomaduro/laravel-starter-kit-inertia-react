import {
    BarChart3,
    Bell,
    BookOpen,
    Bot,
    Building2,
    ClipboardList,
    CloudCog,
    Cog,
    Coins,
    Component,
    Cookie,
    CreditCard,
    Database,
    Download,
    FileText,
    Flag,
    Globe,
    Heart,
    ImageIcon,
    Key,
    Layout,
    Lock,
    Mail,
    Megaphone,
    MessageCircle,
    MessageSquare,
    Network,
    Palette,
    RadioTower,
    Receipt,
    Rocket,
    Route,
    Search,
    Settings,
    Share2,
    Shield,
    Sparkles,
    Table,
    TestTube,
    Timer,
    ToggleLeft,
    TrendingUp,
    Truck,
    Type,
    UserCheck,
    UserCog,
    UserPlus,
    Users,
    Wand2,
    Workflow,
    type LucideIcon,
} from 'lucide-react';

export interface Feature {
    icon: LucideIcon;
    title: string;
    description: string;
    dataPan: string;
}

export interface FeatureGroup {
    label: string;
    features: Feature[];
}

export const featureGroups: FeatureGroup[] = [
    {
        label: 'Authentication & Security',
        features: [
            { icon: Lock, title: 'Two-Factor Auth', description: 'TOTP authenticator support with recovery codes, seamless Fortify integration, and challenge flow.', dataPan: 'welcome-feature-2fa' },
            { icon: Users, title: 'Social Login', description: 'Google and GitHub OAuth out of the box. Admin-toggleable per provider with DB-backed credentials.', dataPan: 'welcome-feature-social-login' },
            { icon: Shield, title: 'Roles & Permissions', description: 'Granular RBAC per organization with Spatie Permissions and Governor for entity-level ownership.', dataPan: 'welcome-feature-rbac' },
            { icon: ClipboardList, title: 'Audit Log', description: 'Every settings change, role grant, and feature toggle is recorded with full actor context.', dataPan: 'welcome-feature-audit-log' },
            { icon: UserPlus, title: 'User Impersonation', description: 'Super-admins can log in as any user for support. Start and stop are logged.', dataPan: 'welcome-feature-impersonation' },
            { icon: Cookie, title: 'Cookie Consent', description: 'GDPR-compliant cookie consent banner with feature-flag control.', dataPan: 'welcome-feature-cookie-consent' },
        ],
    },
    {
        label: 'Multi-tenancy & Organizations',
        features: [
            { icon: Building2, title: 'Organizations & Teams', description: 'Full multi-tenant org management with invitations, member roles, and single-tenant mode.', dataPan: 'welcome-feature-orgs' },
            { icon: Globe, title: 'Custom Domains', description: 'Each org gets its own subdomain. Add custom domains with CNAME verification and SSL.', dataPan: 'welcome-feature-domains' },
            { icon: ToggleLeft, title: 'Feature Flags', description: 'Laravel Pennant with per-org overrides. Plan-gated flags restrict by subscription.', dataPan: 'welcome-feature-flags' },
            { icon: UserCog, title: 'Custom Org Roles', description: 'Org admins create custom roles from safe-to-delegate permissions.', dataPan: 'welcome-feature-custom-roles' },
            { icon: Share2, title: 'Visibility & Sharing', description: 'Global, org-scoped, or shared content with copy-on-write and optional expiry.', dataPan: 'welcome-feature-visibility-sharing' },
        ],
    },
    {
        label: 'Monetization',
        features: [
            { icon: CreditCard, title: 'Subscription Billing', description: 'Stripe, Paddle, and Lemon Squeezy. Plans, trials, seat-based billing, and dunning.', dataPan: 'welcome-feature-billing' },
            { icon: Coins, title: 'Credits System', description: 'One-time credit packs via Lemon Squeezy for AI or metered features.', dataPan: 'welcome-feature-credits' },
        ],
    },
    {
        label: 'AI & Automation',
        features: [
            { icon: MessageCircle, title: 'AI Chat', description: 'Built-in AI chat with conversation memory, streaming, and multi-provider support.', dataPan: 'welcome-feature-ai' },
            { icon: Bot, title: 'MCP Server', description: 'Model Context Protocol server exposes app data to AI assistants with custom tools.', dataPan: 'welcome-feature-mcp' },
            { icon: Workflow, title: 'Durable Workflows', description: 'Long-running saga-style workflows with Waterline monitoring UI.', dataPan: 'welcome-feature-workflows' },
            { icon: Wand2, title: 'AI Wizard', description: 'Describe your app in plain English. The wizard generates models, controllers, pages, and tests.', dataPan: 'welcome-feature-ai-wizard' },
        ],
    },
    {
        label: 'Communication',
        features: [
            { icon: Bell, title: 'Notification Inbox', description: 'Real-time unread count, mark-read, and per-user channel preferences.', dataPan: 'welcome-feature-notifications' },
            { icon: Mail, title: 'Email Templates', description: 'Database-backed templates sent on events. Manage in Filament.', dataPan: 'welcome-feature-email-templates' },
            { icon: Megaphone, title: 'Announcements', description: 'In-app banners with audience targeting, scheduling, and dismissal.', dataPan: 'welcome-feature-announcements' },
            { icon: MessageSquare, title: 'Contact Form', description: 'Feature-flagged contact form with spam protection.', dataPan: 'welcome-feature-contact' },
            { icon: RadioTower, title: 'Broadcasting', description: 'Laravel Reverb and Echo for WebSockets and real-time updates.', dataPan: 'welcome-feature-broadcasting' },
        ],
    },
    {
        label: 'Content & Discovery',
        features: [
            { icon: FileText, title: 'Blog & Changelog', description: 'Full blog and changelog modules with categories and rich content.', dataPan: 'welcome-feature-blog' },
            { icon: BookOpen, title: 'Help Center', description: 'Knowledge-base articles with search, ratings, and categorization.', dataPan: 'welcome-feature-help' },
            { icon: Search, title: 'Full-text Search', description: 'Laravel Scout + Typesense for blazing-fast search across all models.', dataPan: 'welcome-feature-search' },
            { icon: Type, title: 'Rich Text Editor', description: 'Novel and TipTap editors for content creation with image uploads and formatting.', dataPan: 'welcome-feature-rich-editor' },
        ],
    },
    {
        label: 'UI & Customization',
        features: [
            { icon: Palette, title: 'Theming', description: 'Dark/light mode, custom colors, fonts, border radius, and sidebar layout.', dataPan: 'welcome-feature-theming' },
            { icon: ImageIcon, title: 'Org Branding', description: 'Per-organization logo, colors, and favicon applied across the app.', dataPan: 'welcome-feature-org-branding' },
            { icon: Layout, title: 'Page Builder', description: 'Puck-powered drag-and-drop page builder with custom blocks.', dataPan: 'welcome-feature-page-builder' },
            { icon: Sparkles, title: 'Gamification', description: 'Levels and achievements system for user engagement.', dataPan: 'welcome-feature-gamification' },
            { icon: Component, title: '155+ UI Components', description: 'Full shadcn/ui library with Radix primitives — buttons, dialogs, tables, charts, and more.', dataPan: 'welcome-feature-ui-components' },
        ],
    },
    {
        label: 'Admin Panel',
        features: [
            { icon: Layout, title: 'Filament v5', description: 'Full admin panel with resources, widgets, and custom pages. Server-driven UI.', dataPan: 'welcome-feature-filament' },
            { icon: BarChart3, title: 'Dashboard Widgets', description: 'GA4 visitors, system health, and custom widgets on the admin dashboard.', dataPan: 'welcome-feature-admin-widgets' },
            { icon: Settings, title: 'Settings Pages', description: '26 DB-backed settings groups managed via Filament. Per-org overrides for 7 groups.', dataPan: 'welcome-feature-settings-pages' },
        ],
    },
    {
        label: 'Developer Experience',
        features: [
            { icon: BarChart3, title: 'Analytics & Monitoring', description: 'Pan analytics, GA4, Laravel Pulse, Spatie Health, and Horizon.', dataPan: 'welcome-feature-analytics' },
            { icon: Receipt, title: 'Invoice PDF', description: 'Generate and download PDF invoices from billing records.', dataPan: 'welcome-feature-invoice-pdf' },
            { icon: TrendingUp, title: 'GA4 Widget', description: 'Filament dashboard widget for 7-day visitors and top pages.', dataPan: 'welcome-feature-ga4' },
            { icon: Timer, title: 'Cronless Schedule', description: 'Run the Laravel scheduler without cron on PaaS platforms.', dataPan: 'welcome-feature-cronless' },
            { icon: Flag, title: 'Model States & Flags', description: 'Spatie Model States for workflows and Model Flags for featured/pinned.', dataPan: 'welcome-feature-model-states-flags' },
            { icon: Database, title: 'Schemaless Attributes', description: 'Flexible JSON attributes without migrations on key models.', dataPan: 'welcome-feature-schemaless' },
            { icon: Network, title: 'Saloon HTTP Client', description: 'Typed connectors for Paddle, Typesense, and third-party APIs.', dataPan: 'welcome-feature-saloon' },
            { icon: Table, title: 'Server-side DataTables', description: 'Sort, filter, paginate, inline edit, export, quick views, and AI panel.', dataPan: 'welcome-feature-datatables' },
            { icon: Key, title: 'REST API', description: 'Versioned API with Sanctum auth and Scramble OpenAPI docs.', dataPan: 'welcome-feature-api' },
            { icon: Download, title: 'Personal Data Export', description: 'GDPR-ready ZIP export delivered via queued job.', dataPan: 'welcome-feature-data-export' },
            { icon: CloudCog, title: 'Backups', description: 'Spatie Laravel Backup with disk, DB, and Filament management.', dataPan: 'welcome-feature-backups' },
            { icon: Rocket, title: 'CLI Installer', description: 'Interactive CLI installer with presets, AI keys, and demo data.', dataPan: 'welcome-feature-cli-installer' },
            { icon: TestTube, title: '520+ Tests', description: 'Comprehensive Pest test suite with feature and unit tests. 100% coverage target.', dataPan: 'welcome-feature-tests' },
            { icon: Route, title: 'Type-safe Routes', description: 'Laravel Wayfinder generates TypeScript functions for every route. No magic strings.', dataPan: 'welcome-feature-wayfinder' },
            { icon: Cog, title: 'Code Quality', description: 'Rector, PHPStan max, Laravel Pint, ESLint, and Prettier enforced. Zero tolerance for code smells.', dataPan: 'welcome-feature-code-quality' },
            { icon: Heart, title: 'Health Checks', description: 'Spatie Health with scheduled monitoring. Database, queue, disk, and custom checks.', dataPan: 'welcome-feature-health-checks' },
            { icon: UserCheck, title: 'User Onboarding', description: 'Multi-step onboarding flow with spatie/laravel-onboard. Verify email, complete profile, get started.', dataPan: 'welcome-feature-onboarding' },
        ],
    },
];

export const pricingTiers = [
    {
        name: 'Solo',
        price: '$29',
        period: '/mo',
        description: 'For indie developers',
        features: ['All modules included', '1 active project', 'Bring your own AI keys', 'Community support'],
        cta: 'Get started',
        highlighted: false,
    },
    {
        name: 'Agency',
        price: '$99',
        period: '/mo',
        description: 'For freelancers & agencies',
        features: ['All modules included', '5 active projects', '10k AI calls/month', 'Priority support', 'White-label ready'],
        cta: 'Get started',
        highlighted: true,
    },
    {
        name: 'Enterprise',
        price: '$299',
        period: '/mo',
        description: 'For teams & companies',
        features: ['All modules included', 'Unlimited projects', 'Dedicated AI quota', 'Priority support', 'Custom modules', 'SLA'],
        cta: 'Contact us',
        highlighted: false,
    },
];

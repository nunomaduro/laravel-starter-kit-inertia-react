import { dashboard, login, register } from '@/routes';
import { index as blogIndex } from '@/routes/blog';
import { index as changelogIndex } from '@/routes/changelog';
import { create as contactCreate } from '@/routes/contact';
import { index as helpIndex } from '@/routes/help';
import { privacy as legalPrivacy, terms as legalTerms } from '@/routes/legal';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    Bell,
    BookOpen,
    Bot,
    Building2,
    Check,
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
    Terminal,
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

interface Feature {
    icon: LucideIcon;
    title: string;
    description: string;
    dataPan: string;
}

interface FeatureGroup {
    label: string;
    features: Feature[];
}

const featureGroups: FeatureGroup[] = [
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
            { icon: TestTube, title: '150+ Tests', description: 'Comprehensive Pest test suite with feature and unit tests. 100% coverage target.', dataPan: 'welcome-feature-tests' },
            { icon: Route, title: 'Type-safe Routes', description: 'Laravel Wayfinder generates TypeScript functions for every route. No magic strings.', dataPan: 'welcome-feature-wayfinder' },
            { icon: Cog, title: 'Code Quality', description: 'Rector, PHPStan max, Laravel Pint, ESLint, and Prettier enforced. Zero tolerance for code smells.', dataPan: 'welcome-feature-code-quality' },
            { icon: Heart, title: 'Health Checks', description: 'Spatie Health with scheduled monitoring. Database, queue, disk, and custom checks.', dataPan: 'welcome-feature-health-checks' },
            { icon: UserCheck, title: 'User Onboarding', description: 'Multi-step onboarding flow with spatie/laravel-onboard. Verify email, complete profile, get started.', dataPan: 'welcome-feature-onboarding' },
        ],
    },
];

const pricingTiers = [
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

export default function Welcome() {
    const { auth, features: f } = usePage<SharedData>().props;
    const flags = f ?? {};
    const name = usePage<SharedData>().props.name;

    return (
        <>
            <Head title="AI-Native App Factory" />
            <div className="flex min-h-screen flex-col bg-background text-foreground">
                {/* Header */}
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-4">
                        <span className="font-mono text-sm font-semibold tracking-tight">{name}</span>
                        <nav className="flex items-center gap-1">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    data-pan="welcome-dashboard"
                                    className="rounded-md border border-border px-4 py-1.5 text-sm font-medium transition-colors duration-100 hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href="/wizard"
                                        data-pan="welcome-wizard"
                                        className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        Wizard
                                    </Link>
                                    {flags.blog && (
                                        <Link href={blogIndex().url} data-pan="welcome-blog" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Blog
                                        </Link>
                                    )}
                                    {flags.changelog && (
                                        <Link href={changelogIndex().url} data-pan="welcome-changelog" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Changelog
                                        </Link>
                                    )}
                                    {flags.help && (
                                        <Link href={helpIndex().url} data-pan="welcome-help" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Help
                                        </Link>
                                    )}
                                    {flags.contact && (
                                        <Link href={contactCreate().url} data-pan="welcome-contact" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Contact
                                        </Link>
                                    )}
                                    <Link href={login()} data-pan="welcome-log-in" className="rounded-md px-4 py-1.5 text-sm text-muted-foreground transition-colors duration-100 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                        Log in
                                    </Link>
                                    {flags.registration && (
                                        <Link href={register()} data-pan="welcome-register" className="rounded-md border border-border bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors duration-100 hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Get started
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero */}
                <section className="mx-auto flex w-full max-w-5xl flex-col px-6 pt-24 pb-20">
                    <span className="mb-6 font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// STARTER KIT</span>
                    <h1 className="font-mono text-4xl font-bold tracking-tight sm:text-5xl" style={{ letterSpacing: '-0.03em' }}>
                        Build AI-native corporate apps
                        <br />
                        <span className="text-primary">in minutes, not months</span>
                    </h1>
                    <p className="mt-5 max-w-xl text-base leading-relaxed text-muted-foreground">
                        Skip 3 months of infrastructure. Describe your app, select modules, ship to production — powered by 70+ packages and an AI assistant that knows your domain.
                    </p>
                    <div className="mt-8 flex flex-wrap items-center gap-3">
                        <Link
                            href="/wizard"
                            data-pan="welcome-hero-wizard"
                            className="rounded-md bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground transition-colors duration-100 hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <Wand2 className="mr-2 inline h-4 w-4" />
                            Launch the Wizard
                        </Link>
                        <Link
                            href={login()}
                            data-pan="welcome-log-in"
                            className="rounded-md border border-border px-6 py-2.5 text-sm font-medium transition-colors duration-100 hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            Log in
                        </Link>
                    </div>
                    <div className="mt-10 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-xs text-muted-foreground">
                        <span>Laravel 13</span>
                        <span className="text-border">·</span>
                        <span>Inertia v2</span>
                        <span className="text-border">·</span>
                        <span>React 19</span>
                        <span className="text-border">·</span>
                        <span>Tailwind CSS v4</span>
                        <span className="text-border">·</span>
                        <span>Filament v5</span>
                        <span className="text-border">·</span>
                        <span>Laravel AI SDK</span>
                        <span className="text-border">·</span>
                        <span>TypeScript</span>
                    </div>
                </section>

                <div className="h-px w-full bg-border" />

                {/* How it works */}
                <section className="mx-auto w-full max-w-5xl px-6 py-16">
                    <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// HOW IT WORKS</span>
                    <h2 className="mb-10 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>From idea to production in three steps</h2>
                    <div className="grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-3">
                        {[
                            { step: '01', icon: MessageSquare, title: 'Describe', description: 'Tell the AI wizard what you want to build — HR system, CRM, fleet tracker. Plain English.' },
                            { step: '02', icon: Wand2, title: 'Generate', description: 'The factory analyzes your description, selects modules, and scaffolds models, controllers, pages, and tests.' },
                            { step: '03', icon: Rocket, title: 'Ship', description: 'Multi-tenancy, billing, auth, and AI chat are already wired. Deploy your production-ready app.' },
                        ].map((item) => (
                            <div key={item.step} className="bg-card p-6" data-pan={`welcome-step-${item.step}`}>
                                <span className="font-mono text-xs text-muted-foreground">{item.step}</span>
                                <item.icon className="mt-3 mb-3 h-5 w-5 text-primary" />
                                <h3 className="font-mono text-sm font-semibold tracking-tight">{item.title}</h3>
                                <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{item.description}</p>
                            </div>
                        ))}
                    </div>
                </section>

                <div className="h-px w-full bg-border" />

                {/* Module showcase */}
                <section className="mx-auto w-full max-w-5xl px-6 py-16">
                    <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// MODULES</span>
                    <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Pre-built Domain Modules</h2>
                    <p className="mb-10 text-sm text-muted-foreground">Install in seconds, customize everything</p>
                    <div className="grid gap-4 sm:grid-cols-3">
                        {[
                            { icon: Users, name: 'HR', title: 'Human Resources', description: 'Employee management, departments, leave tracking, attendance, and performance reviews.', dataPan: 'welcome-module-hr' },
                            { icon: TrendingUp, name: 'CRM', title: 'Customer Relationship Management', description: 'Contact management, deal tracking, sales pipelines, and activity logging.', dataPan: 'welcome-module-crm' },
                            { icon: Truck, name: 'Fleet', title: 'Fleet Management', description: 'Vehicle tracking, driver management, maintenance scheduling, and compliance.', dataPan: 'welcome-module-fleet' },
                        ].map((mod) => (
                            <Link
                                key={mod.name}
                                href="/showcase"
                                className="group rounded-lg border border-border bg-card p-6 transition-colors duration-100 hover:bg-accent"
                                data-pan={mod.dataPan}
                            >
                                <mod.icon className="mb-3 h-5 w-5 text-primary" />
                                <h3 className="font-mono text-sm font-semibold tracking-tight">{mod.title}</h3>
                                <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{mod.description}</p>
                            </Link>
                        ))}
                    </div>
                    <p className="mt-8 text-sm text-muted-foreground">
                        Or create your own:{' '}
                        <code className="rounded bg-muted px-2 py-1 font-mono text-xs">php artisan make:module YourModel</code>
                        {' '}— scaffolds 18 files in one command
                    </p>
                </section>

                {/* Stats bar */}
                <section className="border-y border-border bg-muted/40 py-10" data-pan="welcome-stats">
                    <div className="mx-auto flex max-w-3xl flex-wrap items-center justify-between gap-8 px-6">
                        {[
                            { value: '70+', label: 'Packages' },
                            { value: '30+', label: 'Models' },
                            { value: '18', label: 'Files per module' },
                            { value: '155+', label: 'UI Components' },
                        ].map((stat) => (
                            <div key={stat.label}>
                                <div className="font-mono text-2xl font-bold tracking-tight">{stat.value}</div>
                                <div className="text-xs text-muted-foreground">{stat.label}</div>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Built with */}
                <section className="mx-auto w-full max-w-5xl px-6 py-16">
                    <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// BUILT WITH</span>
                    <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Production-grade stack</h2>
                    <p className="mb-10 text-sm text-muted-foreground">Every dependency is battle-tested and actively maintained</p>
                    <div className="grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            { name: 'Laravel 13', detail: 'PHP 8.4, strict types' },
                            { name: 'React 19', detail: 'Inertia v2, TypeScript' },
                            { name: 'Filament v5', detail: 'Admin panel, SDUI' },
                            { name: 'Tailwind v4', detail: '155+ shadcn components' },
                            { name: 'Laravel AI SDK', detail: 'Agents, tools, streaming' },
                            { name: 'Pest 4', detail: '150+ tests, full coverage' },
                            { name: 'Horizon + Pulse', detail: 'Queues, monitoring' },
                            { name: 'Scout + Typesense', detail: 'Full-text search' },
                        ].map((tech) => (
                            <div key={tech.name} className="bg-card p-4">
                                <div className="font-mono text-sm font-semibold tracking-tight">{tech.name}</div>
                                <div className="mt-1 text-xs text-muted-foreground">{tech.detail}</div>
                            </div>
                        ))}
                    </div>
                </section>

                <div className="h-px w-full bg-border" />

                {/* Key differentiators */}
                <section className="mx-auto w-full max-w-5xl px-6 py-16">
                    <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// WHY THIS</span>
                    <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>What makes this different</h2>
                    <p className="mb-10 text-sm text-muted-foreground">Not just a starter kit — an AI-powered app factory</p>
                    <div className="grid gap-4 sm:grid-cols-3">
                        <Link href="/chat" className="rounded-lg border border-border bg-card p-6 transition-colors duration-100 hover:bg-accent" data-pan="welcome-diff-ai">
                            <Bot className="mb-3 h-5 w-5 text-primary" />
                            <h3 className="font-mono text-sm font-semibold tracking-tight">AI Assistant</h3>
                            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                                A built-in AI chat that understands your domain. Ask questions about your data, get help with code, or guide your users. Multi-provider with memory and RAG.
                            </p>
                        </Link>
                        <Link href="/showcase" className="rounded-lg border border-border bg-card p-6 transition-colors duration-100 hover:bg-accent" data-pan="welcome-diff-modules">
                            <Wand2 className="mb-3 h-5 w-5 text-primary" />
                            <h3 className="font-mono text-sm font-semibold tracking-tight">Module System</h3>
                            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                                Pre-built domain modules (HR, CRM, Fleet) with full CRUD, admin panel, Inertia pages, and tests. Cross-module AI intelligence included.
                            </p>
                        </Link>
                        <div className="rounded-lg border border-border bg-card p-6" data-pan="welcome-diff-scaffold">
                            <Terminal className="mb-3 h-5 w-5 text-primary" />
                            <h3 className="font-mono text-sm font-semibold tracking-tight">One-Command Scaffolding</h3>
                            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                                Run <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">make:module</code> for 18 files or{' '}
                                <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">factory:create</code> with AI analysis. From description to app in minutes.
                            </p>
                        </div>
                    </div>
                </section>

                <div className="h-px w-full bg-border" />

                {/* Feature grid (condensed) */}
                <section className="mx-auto w-full max-w-5xl space-y-10 px-6 py-16">
                    <div>
                        <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// FEATURES</span>
                        <h2 className="font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>60+ features, all production-ready</h2>
                        <p className="mt-2 text-sm text-muted-foreground">Everything you need across 9 domains</p>
                    </div>
                    {featureGroups.map((group) => (
                        <div key={group.label}>
                            <h3 className="mb-4 font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-muted-foreground">{group.label}</h3>
                            <div className="grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-2 lg:grid-cols-4">
                                {group.features.map((feature) => (
                                    <div key={feature.title} className="bg-card p-4" data-pan={feature.dataPan}>
                                        <feature.icon className="mb-2 size-4 text-primary" />
                                        <h4 className="font-mono text-xs font-semibold tracking-tight">{feature.title}</h4>
                                        <p className="mt-1 text-xs leading-relaxed text-muted-foreground">{feature.description}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </section>

                <div className="h-px w-full bg-border" />

                {/* Comparison */}
                <section className="mx-auto w-full max-w-5xl px-6 py-16">
                    <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// COMPARISON</span>
                    <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Skip 3-6 months of setup</h2>
                    <p className="mb-10 text-sm text-muted-foreground">What you get out of the box vs building from scratch</p>
                    <div className="grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-3">
                        {[
                            { feature: 'Multi-tenant auth + RBAC', scratch: '3-4 weeks', kit: 'Day 1' },
                            { feature: 'Billing (Stripe + seats)', scratch: '2-3 weeks', kit: 'Day 1' },
                            { feature: 'Admin panel', scratch: '2-4 weeks', kit: 'Day 1' },
                            { feature: 'AI chat + MCP server', scratch: '2-3 weeks', kit: 'Day 1' },
                            { feature: 'Real-time notifications', scratch: '1-2 weeks', kit: 'Day 1' },
                            { feature: 'Full test suite', scratch: '2-3 weeks', kit: 'Day 1' },
                        ].map((row) => (
                            <div key={row.feature} className="flex items-center justify-between bg-card p-4">
                                <span className="text-sm">{row.feature}</span>
                                <div className="flex items-center gap-3 font-mono text-xs">
                                    <span className="text-muted-foreground line-through">{row.scratch}</span>
                                    <span className="font-semibold text-primary">{row.kit}</span>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                <div className="h-px w-full bg-border" />

                {/* Pricing */}
                <section className="mx-auto w-full max-w-5xl px-6 py-16">
                    <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// PRICING</span>
                    <h2 className="mb-2 font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Simple pricing</h2>
                    <p className="mb-10 text-sm text-muted-foreground">Start building today. Scale when you're ready.</p>
                    <div className="grid gap-4 sm:grid-cols-3">
                        {pricingTiers.map((tier) => (
                            <div
                                key={tier.name}
                                className={`relative rounded-lg border p-6 ${tier.highlighted ? 'border-primary bg-primary/5' : 'border-border bg-card'}`}
                                data-pan={`welcome-pricing-${tier.name.toLowerCase()}`}
                            >
                                {tier.highlighted && (
                                    <span className="absolute -top-3 left-4 rounded-full bg-primary px-3 py-0.5 font-mono text-[11px] font-medium text-primary-foreground">
                                        Most popular
                                    </span>
                                )}
                                <h3 className="font-mono text-base font-semibold tracking-tight">{tier.name}</h3>
                                <p className="text-sm text-muted-foreground">{tier.description}</p>
                                <div className="mt-4">
                                    <span className="font-mono text-3xl font-bold tracking-tight">{tier.price}</span>
                                    <span className="text-sm text-muted-foreground">{tier.period}</span>
                                </div>
                                <ul className="mt-6 space-y-2">
                                    {tier.features.map((f) => (
                                        <li key={f} className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Check className="h-3.5 w-3.5 shrink-0 text-primary" />
                                            {f}
                                        </li>
                                    ))}
                                </ul>
                                <Link
                                    href={tier.name === 'Enterprise' ? '/contact' : register()}
                                    data-pan={`welcome-pricing-${tier.name.toLowerCase()}-cta`}
                                    className={`mt-6 block rounded-md px-4 py-2 text-center text-sm font-medium transition-colors duration-100 ${
                                        tier.highlighted
                                            ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                                            : 'border border-border hover:bg-accent'
                                    }`}
                                >
                                    {tier.cta}
                                </Link>
                            </div>
                        ))}
                    </div>
                </section>

                <div className="h-px w-full bg-border" />

                {/* Final CTA */}
                <section className="mx-auto w-full max-w-5xl px-6 py-16">
                    <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// GET STARTED</span>
                    <h2 className="font-mono text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Ready to build your next app?</h2>
                    <p className="mt-2 text-sm text-muted-foreground">Describe your idea and let the AI Factory do the rest.</p>
                    <div className="mt-8 flex flex-wrap items-center gap-4">
                        <Link
                            href="/wizard"
                            data-pan="welcome-cta-wizard"
                            className="rounded-md bg-primary px-8 py-3 text-sm font-semibold text-primary-foreground transition-colors duration-100 hover:bg-primary/90"
                        >
                            <Wand2 className="mr-2 inline h-4 w-4" />
                            Open the Wizard
                        </Link>
                        <div className="rounded-md border border-border bg-muted px-4 py-2.5 font-mono text-xs text-muted-foreground" data-pan="welcome-cta-cli">
                            <Terminal className="mr-2 inline h-3.5 w-3.5" />
                            php artisan factory:create &quot;An HR system for a logistics company&quot;
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-border py-6 text-sm text-muted-foreground">
                    <div className="mx-auto max-w-5xl px-6">
                        <Link href={legalTerms().url} className="transition-colors duration-100 hover:text-foreground">
                            Terms of Service
                        </Link>
                        {' · '}
                        <Link href={legalPrivacy().url} className="transition-colors duration-100 hover:text-foreground">
                            Privacy Policy
                        </Link>
                    </div>
                </footer>
            </div>
        </>
    );
}

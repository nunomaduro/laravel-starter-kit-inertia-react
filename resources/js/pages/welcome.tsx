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
    ClipboardList,
    CloudCog,
    Coins,
    CreditCard,
    Database,
    Download,
    FileText,
    Flag,
    Globe,
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
    Search,
    Share2,
    Shield,
    Sparkles,
    Table,
    Timer,
    ToggleLeft,
    TrendingUp,
    UserCog,
    UserPlus,
    Users,
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
            {
                icon: Lock,
                title: 'Two-Factor Auth',
                description:
                    'TOTP authenticator support with recovery codes, seamless Fortify integration, and challenge flow.',
                dataPan: 'welcome-feature-2fa',
            },
            {
                icon: Users,
                title: 'Social Login',
                description:
                    'Google and GitHub OAuth out of the box. Admin-toggleable per provider with DB-backed credentials.',
                dataPan: 'welcome-feature-social-login',
            },
            {
                icon: Shield,
                title: 'Roles & Permissions',
                description:
                    'Granular RBAC per organization with Spatie Permissions and Governor for entity-level ownership. Custom org roles and invite guards.',
                dataPan: 'welcome-feature-rbac',
            },
            {
                icon: ClipboardList,
                title: 'Audit Log',
                description:
                    'Every settings change, role grant, and feature toggle is recorded. Org admins see their own log; system admins see all.',
                dataPan: 'welcome-feature-audit-log',
            },
            {
                icon: UserPlus,
                title: 'User Impersonation',
                description:
                    'Super-admins can log in as any user for support. Start and stop are logged; super-admins cannot be impersonated.',
                dataPan: 'welcome-feature-impersonation',
            },
        ],
    },
    {
        label: 'Multi-tenancy & Organizations',
        features: [
            {
                icon: Building2,
                title: 'Organizations & Teams',
                description:
                    'Full multi-tenant org management with invitations, member roles, per-org settings, and single-tenant mode.',
                dataPan: 'welcome-feature-orgs',
            },
            {
                icon: Globe,
                title: 'Custom Domains',
                description:
                    'Each org gets its own subdomain. Add custom domains with CNAME-based DNS verification, SSL, and 301 redirect history.',
                dataPan: 'welcome-feature-domains',
            },
            {
                icon: ToggleLeft,
                title: 'Feature Flags',
                description:
                    'Laravel Pennant feature flags with per-org overrides. Admins can toggle features per org; plan-gated flags restrict by subscription.',
                dataPan: 'welcome-feature-flags',
            },
            {
                icon: UserCog,
                title: 'Custom Org Roles',
                description:
                    'Org admins create custom roles from safe-to-delegate permissions. Role templates ship out of the box.',
                dataPan: 'welcome-feature-custom-roles',
            },
            {
                icon: Share2,
                title: 'Visibility & Sharing',
                description:
                    'Global, org-scoped, or shared content with copy-on-write. Share items with view/edit access and optional expiry.',
                dataPan: 'welcome-feature-visibility-sharing',
            },
        ],
    },
    {
        label: 'Monetization',
        features: [
            {
                icon: CreditCard,
                title: 'Subscription Billing',
                description:
                    'Stripe, Paddle, and Lemon Squeezy. Plans, trials, seat-based billing, and a unified billing dashboard.',
                dataPan: 'welcome-feature-billing',
            },
            {
                icon: Coins,
                title: 'Credits System',
                description:
                    'One-time credit packs via Lemon Squeezy. Credits track usage for AI or metered features with a full purchase history.',
                dataPan: 'welcome-feature-credits',
            },
        ],
    },
    {
        label: 'AI & Automation',
        features: [
            {
                icon: MessageCircle,
                title: 'AI Chat',
                description:
                    'Built-in AI chat with conversation memory, streaming responses, and multi-provider support via the Laravel AI SDK.',
                dataPan: 'welcome-feature-ai',
            },
            {
                icon: Bot,
                title: 'MCP Server',
                description:
                    'A built-in Model Context Protocol server exposes app data to AI assistants. Easily extend with custom tools and resources.',
                dataPan: 'welcome-feature-mcp',
            },
            {
                icon: Workflow,
                title: 'Durable Workflows',
                description:
                    'Long-running saga-style workflows with laravel-workflow. Monitor and debug via the Waterline UI at /waterline.',
                dataPan: 'welcome-feature-workflows',
            },
        ],
    },
    {
        label: 'Communication',
        features: [
            {
                icon: Bell,
                title: 'Notification Inbox',
                description:
                    'In-app notification bell with real-time unread count, mark-read, and clear-all. Per-user channel preferences for in-app and email.',
                dataPan: 'welcome-feature-notifications',
            },
            {
                icon: Mail,
                title: 'Database Email Templates',
                description:
                    'Email templates stored in the database and sent on events. Optional mail tracking (Laravel Mails). Manage in Filament.',
                dataPan: 'welcome-feature-email-templates',
            },
            {
                icon: Megaphone,
                title: 'Announcements',
                description:
                    'In-app announcement banners with audience targeting. Create and schedule in Filament; dismissible by users.',
                dataPan: 'welcome-feature-announcements',
            },
            {
                icon: MessageSquare,
                title: 'Contact Form',
                description:
                    'Feature-flagged contact form with spam protection. Submissions stored and manageable from the admin panel.',
                dataPan: 'welcome-feature-contact',
            },
            {
                icon: RadioTower,
                title: 'Real-time Broadcasting',
                description:
                    'Laravel Reverb and Echo for WebSockets. Real-time notifications and live updates; optional installer step.',
                dataPan: 'welcome-feature-broadcasting',
            },
        ],
    },
    {
        label: 'Content & Discovery',
        features: [
            {
                icon: FileText,
                title: 'Blog & Changelog',
                description:
                    'Full blog and changelog modules with categories, rich content, and public-facing pages. Feature-flagged.',
                dataPan: 'welcome-feature-blog',
            },
            {
                icon: BookOpen,
                title: 'Help Center',
                description:
                    'Knowledge-base articles with search, ratings, and categorization. Useful or not? Users can vote.',
                dataPan: 'welcome-feature-help',
            },
            {
                icon: Search,
                title: 'Full-text Search',
                description:
                    'Laravel Scout + Typesense for blazing-fast full-text search. Add the Searchable trait to any model.',
                dataPan: 'welcome-feature-search',
            },
        ],
    },
    {
        label: 'UI & Customization',
        features: [
            {
                icon: Palette,
                title: 'Appearance & Theming',
                description:
                    'Dark/light mode, custom primary color, font, border radius, and sidebar layout. System admin can lock settings; org admins control what users can change.',
                dataPan: 'welcome-feature-theming',
            },
            {
                icon: ImageIcon,
                title: 'Org Branding',
                description:
                    'Per-organization logo, colors, and favicon. Each tenant can customize their branding; applied across the app when in context.',
                dataPan: 'welcome-feature-org-branding',
            },
            {
                icon: Layout,
                title: 'Page Builder',
                description:
                    'Puck-powered drag-and-drop page builder with custom blocks and a live preview editor.',
                dataPan: 'welcome-feature-page-builder',
            },
            {
                icon: Sparkles,
                title: 'Gamification',
                description:
                    'Levels and achievements system. Reward users for reaching milestones and engaging with the app.',
                dataPan: 'welcome-feature-gamification',
            },
        ],
    },
    {
        label: 'Developer Experience',
        features: [
            {
                icon: BarChart3,
                title: 'Analytics & Monitoring',
                description:
                    'Pan product analytics (impressions, hovers, clicks), GA4 widget on Filament dashboard, Laravel Pulse for real-time monitoring, Spatie Health for checks and alerts, and Horizon for queue monitoring.',
                dataPan: 'welcome-feature-analytics',
            },
            {
                icon: Receipt,
                title: 'Invoice PDF',
                description:
                    'LaravelDaily Invoices for billing: generate and download PDFs from app invoices. BuildLaravelDailyInvoice action and tenant-scoped download route.',
                dataPan: 'welcome-feature-invoice-pdf',
            },
            {
                icon: TrendingUp,
                title: 'GA4 Widget',
                description:
                    'Filament dashboard widget for Spatie Laravel Analytics: 7-day visitors and top pages. Optional; requires analytics property ID.',
                dataPan: 'welcome-feature-ga4',
            },
            {
                icon: Timer,
                title: 'Cronless Schedule',
                description:
                    'Run the Laravel scheduler without cron (e.g. on PaaS). Use composer schedule:cronless or schedule:run-cronless; Procfile example in deployment docs.',
                dataPan: 'welcome-feature-cronless',
            },
            {
                icon: Flag,
                title: 'Model States & Flags',
                description:
                    'Spatie Model States for RefundRequest, OrganizationInvitation, Affiliate flows. Model Flags for HelpArticle (featured, pinned) and Announcement/Post (featured).',
                dataPan: 'welcome-feature-model-states-flags',
            },
            {
                icon: Database,
                title: 'Schemaless Attributes',
                description:
                    'Spatie Schemaless Attributes on Credit, Organization, and Page (extra_attributes) for flexible JSON without migrations.',
                dataPan: 'welcome-feature-schemaless',
            },
            {
                icon: Network,
                title: 'Saloon HTTP Client',
                description:
                    'Paddle and Typesense connectors via Saloon. Typed requests, health checks, and gateway integration for third-party APIs.',
                dataPan: 'welcome-feature-saloon',
            },
            {
                icon: Table,
                title: 'Server-side DataTables',
                description:
                    'Full-featured tables: sort, filter, pagination, inline edit, toggle, reorder, export/import, quick views, detail row, soft deletes, cascading filters. Optional AI panel (NLQ, insights, suggest, column summary, enrich) and Thesys Visualize when configured.',
                dataPan: 'welcome-feature-datatables',
            },
            {
                icon: Key,
                title: 'REST API',
                description:
                    'Versioned REST API (v1) with Sanctum token auth, Scramble-generated OpenAPI docs, and per-resource rate limiting.',
                dataPan: 'welcome-feature-api',
            },
            {
                icon: Download,
                title: 'Personal Data Export',
                description:
                    'GDPR-ready personal data export. Users request a ZIP of their data; delivered asynchronously via queued job.',
                dataPan: 'welcome-feature-data-export',
            },
            {
                icon: CloudCog,
                title: 'Backups',
                description:
                    'Spatie Laravel Backup with disk, DB, and optional notifications. Manage and monitor from Filament.',
                dataPan: 'welcome-feature-backups',
            },
            {
                icon: Rocket,
                title: 'CLI Installer',
                description:
                    'Interactive CLI installer with presets, AI keys, tenancy, and demo data. One command to go live.',
                dataPan: 'welcome-feature-cli-installer',
            },
        ],
    },
];

export default function Welcome() {
    const { auth, features: f } = usePage<SharedData>().props;
    const flags = f ?? {};
    const name = usePage<SharedData>().props.name;

    return (
        <>
            <Head title="Welcome" />
            <div className="flex min-h-screen flex-col bg-background text-foreground">
                {/* Header */}
                <header className="border-b border-border/60">
                    <div className="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-4">
                        <span className="font-semibold tracking-tight">
                            {name}
                        </span>
                        <nav className="flex items-center gap-1">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    data-pan="welcome-dashboard"
                                    className="rounded-md border border-border px-4 py-1.5 text-sm font-medium transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    {flags.blog && (
                                        <Link
                                            href={blogIndex().url}
                                            data-pan="welcome-blog"
                                            className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        >
                                            Blog
                                        </Link>
                                    )}
                                    {flags.changelog && (
                                        <Link
                                            href={changelogIndex().url}
                                            data-pan="welcome-changelog"
                                            className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        >
                                            Changelog
                                        </Link>
                                    )}
                                    {flags.help && (
                                        <Link
                                            href={helpIndex().url}
                                            data-pan="welcome-help"
                                            className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        >
                                            Help
                                        </Link>
                                    )}
                                    {flags.contact && (
                                        <Link
                                            href={contactCreate().url}
                                            data-pan="welcome-contact"
                                            className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        >
                                            Contact
                                        </Link>
                                    )}
                                    <Link
                                        href={login()}
                                        data-pan="welcome-log-in"
                                        className="rounded-md px-4 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        Log in
                                    </Link>
                                    {flags.registration && (
                                        <Link
                                            href={register()}
                                            data-pan="welcome-register"
                                            className="rounded-md border border-border bg-foreground px-4 py-1.5 text-sm font-medium text-background transition-colors hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                        >
                                            Get started
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero */}
                <section className="relative mx-auto flex w-full max-w-5xl flex-col items-center justify-center px-6 py-24 text-center">
                    <div className="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center">
                        <div className="h-[600px] w-[600px] rounded-full bg-primary/5 blur-3xl" />
                    </div>
                    <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                        The modern SaaS starter kit
                    </h1>
                    <p className="mt-4 max-w-xl text-lg text-muted-foreground">
                        Everything you need to ship a production-ready SaaS —
                        multi-tenancy, billing, AI, auth, and more — built on
                        Laravel & React.
                    </p>
                    <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                        {flags.registration && (
                            <Link
                                href={register()}
                                data-pan="welcome-register"
                                className="rounded-md bg-foreground px-6 py-2.5 text-sm font-semibold text-background transition-colors hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                Get started →
                            </Link>
                        )}
                        <Link
                            href={login()}
                            data-pan="welcome-log-in"
                            className="rounded-md border border-border px-6 py-2.5 text-sm font-medium transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            Log in
                        </Link>
                    </div>

                    <p className="mt-8 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
                        <span>Laravel 12</span>
                        <span className="text-border">·</span>
                        <span>Inertia v2</span>
                        <span className="text-border">·</span>
                        <span>React 19</span>
                        <span className="text-border">·</span>
                        <span>Tailwind CSS v4</span>
                        <span className="text-border">·</span>
                        <span>Filament v5</span>
                        <span className="text-border">·</span>
                        <span>Wayfinder</span>
                        <span className="text-border">·</span>
                        <span>TypeScript</span>
                    </p>
                </section>

                <div className="h-px w-full bg-gradient-to-r from-transparent via-border to-transparent" />

                {/* Features by category */}
                <section className="mx-auto w-full max-w-5xl space-y-16 px-6 py-24">
                    {featureGroups.map((group) => (
                        <div key={group.label}>
                            <h2 className="mb-6 text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                                {group.label}
                            </h2>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                {group.features.map((feature) => (
                                    <div
                                        key={feature.title}
                                        className="rounded-xl border border-border bg-card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                                        data-pan={feature.dataPan}
                                    >
                                        <feature.icon className="mb-3 size-5 text-primary" />
                                        <h3 className="text-sm font-semibold">
                                            {feature.title}
                                        </h3>
                                        <p className="mt-1 text-xs leading-relaxed text-muted-foreground">
                                            {feature.description}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </section>

                {/* Footer */}
                <footer className="border-t border-border/60 py-6 text-center text-sm text-muted-foreground">
                    <Link
                        href={legalTerms().url}
                        className="hover:text-foreground hover:underline"
                    >
                        Terms of Service
                    </Link>
                    {' · '}
                    <Link
                        href={legalPrivacy().url}
                        className="hover:text-foreground hover:underline"
                    >
                        Privacy Policy
                    </Link>
                </footer>
            </div>
        </>
    );
}

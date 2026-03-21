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
    Terminal,
    Timer,
    ToggleLeft,
    TrendingUp,
    Truck,
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
        ],
    },
    {
        label: 'UI & Customization',
        features: [
            { icon: Palette, title: 'Theming', description: 'Dark/light mode, custom colors, fonts, border radius, and sidebar layout.', dataPan: 'welcome-feature-theming' },
            { icon: ImageIcon, title: 'Org Branding', description: 'Per-organization logo, colors, and favicon applied across the app.', dataPan: 'welcome-feature-org-branding' },
            { icon: Layout, title: 'Page Builder', description: 'Puck-powered drag-and-drop page builder with custom blocks.', dataPan: 'welcome-feature-page-builder' },
            { icon: Sparkles, title: 'Gamification', description: 'Levels and achievements system for user engagement.', dataPan: 'welcome-feature-gamification' },
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
                <header className="border-b border-border/60">
                    <div className="mx-auto flex max-w-5xl items-center justify-between gap-4 px-6 py-4">
                        <span className="font-semibold tracking-tight">{name}</span>
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
                                    <Link
                                        href="/wizard"
                                        data-pan="welcome-wizard"
                                        className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    >
                                        Wizard
                                    </Link>
                                    {flags.blog && (
                                        <Link href={blogIndex().url} data-pan="welcome-blog" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Blog
                                        </Link>
                                    )}
                                    {flags.changelog && (
                                        <Link href={changelogIndex().url} data-pan="welcome-changelog" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Changelog
                                        </Link>
                                    )}
                                    {flags.help && (
                                        <Link href={helpIndex().url} data-pan="welcome-help" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Help
                                        </Link>
                                    )}
                                    {flags.contact && (
                                        <Link href={contactCreate().url} data-pan="welcome-contact" className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                            Contact
                                        </Link>
                                    )}
                                    <Link href={login()} data-pan="welcome-log-in" className="rounded-md px-4 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
                                        Log in
                                    </Link>
                                    {flags.registration && (
                                        <Link href={register()} data-pan="welcome-register" className="rounded-md border border-border bg-foreground px-4 py-1.5 text-sm font-medium text-background transition-colors hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none">
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
                        Build AI-native corporate apps
                        <br />
                        <span className="text-primary">in minutes, not months</span>
                    </h1>
                    <p className="mt-4 max-w-2xl text-lg text-muted-foreground">
                        Skip 3 months of infrastructure. Describe your app, select modules, ship to production — powered by 70+ packages and an AI assistant that knows your domain.
                    </p>
                    <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                        <Link
                            href="/wizard"
                            data-pan="welcome-hero-wizard"
                            className="rounded-md bg-foreground px-6 py-2.5 text-sm font-semibold text-background transition-colors hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <Wand2 className="mr-2 inline h-4 w-4" />
                            Launch the Wizard →
                        </Link>
                        <Link
                            href={login()}
                            data-pan="welcome-log-in"
                            className="rounded-md border border-border px-6 py-2.5 text-sm font-medium transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            Log in
                        </Link>
                    </div>
                    <p className="mt-8 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
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
                    </p>
                </section>

                <div className="h-px w-full bg-gradient-to-r from-transparent via-border to-transparent" />

                {/* How it works */}
                <section className="mx-auto w-full max-w-5xl px-6 py-20">
                    <h2 className="mb-2 text-center text-2xl font-bold">How it works</h2>
                    <p className="mb-12 text-center text-muted-foreground">From idea to production in three steps</p>
                    <div className="grid gap-6 sm:grid-cols-3">
                        {[
                            { step: 1, icon: MessageSquare, title: 'Describe', description: 'Tell the AI wizard what you want to build — HR system, CRM, fleet tracker. Plain English.' },
                            { step: 2, icon: Wand2, title: 'Generate', description: 'The factory analyzes your description, selects modules, and scaffolds models, controllers, pages, and tests.' },
                            { step: 3, icon: Rocket, title: 'Ship', description: 'Multi-tenancy, billing, auth, and AI chat are already wired. Deploy your production-ready app.' },
                        ].map((item) => (
                            <div key={item.step} className="rounded-xl border border-border bg-card p-6 text-center" data-pan={`welcome-step-${item.step}`}>
                                <div className="mx-auto mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
                                    {item.step}
                                </div>
                                <item.icon className="mx-auto mb-3 h-6 w-6 text-muted-foreground" />
                                <h3 className="font-semibold">{item.title}</h3>
                                <p className="mt-2 text-sm text-muted-foreground">{item.description}</p>
                            </div>
                        ))}
                    </div>
                </section>

                <div className="h-px w-full bg-gradient-to-r from-transparent via-border to-transparent" />

                {/* Module showcase */}
                <section className="mx-auto w-full max-w-5xl px-6 py-20">
                    <h2 className="mb-2 text-center text-2xl font-bold">Pre-built Domain Modules</h2>
                    <p className="mb-12 text-center text-muted-foreground">Install in seconds, customize everything</p>
                    <div className="grid gap-6 sm:grid-cols-3">
                        {[
                            { icon: Users, name: 'HR', title: 'Human Resources', description: 'Employee management, departments, leave tracking, attendance, and performance reviews.', dataPan: 'welcome-module-hr' },
                            { icon: TrendingUp, name: 'CRM', title: 'Customer Relationship Management', description: 'Contact management, deal tracking, sales pipelines, and activity logging.', dataPan: 'welcome-module-crm' },
                            { icon: Truck, name: 'Fleet', title: 'Fleet Management', description: 'Vehicle tracking, driver management, maintenance scheduling, and compliance.', dataPan: 'welcome-module-fleet' },
                        ].map((mod) => (
                            <Link
                                key={mod.name}
                                href="/showcase"
                                className="group rounded-xl border-l-4 border-l-primary border border-border bg-card p-6 transition-all hover:-translate-y-0.5 hover:shadow-md"
                                data-pan={mod.dataPan}
                            >
                                <mod.icon className="mb-3 h-7 w-7 text-primary" />
                                <h3 className="font-semibold">{mod.title}</h3>
                                <p className="mt-2 text-sm text-muted-foreground">{mod.description}</p>
                            </Link>
                        ))}
                    </div>
                    <p className="mt-8 text-center text-sm text-muted-foreground">
                        Or create your own:{' '}
                        <code className="rounded bg-muted px-2 py-1 font-mono text-xs">php artisan make:module YourModel</code>
                        {' '}— scaffolds 18 files in one command
                    </p>
                </section>

                {/* Stats bar */}
                <section className="bg-muted/30 py-10" data-pan="welcome-stats">
                    <div className="mx-auto flex max-w-3xl flex-wrap items-center justify-center gap-12 px-6">
                        {[
                            { value: '70+', label: 'Packages' },
                            { value: '30+', label: 'Models' },
                            { value: '18', label: 'Files per module' },
                            { value: '3', label: 'Domain modules' },
                        ].map((stat) => (
                            <div key={stat.label} className="text-center">
                                <div className="text-3xl font-bold">{stat.value}</div>
                                <div className="text-sm text-muted-foreground">{stat.label}</div>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Key differentiators */}
                <section className="mx-auto w-full max-w-5xl px-6 py-20">
                    <h2 className="mb-2 text-center text-2xl font-bold">What makes this different</h2>
                    <p className="mb-12 text-center text-muted-foreground">Not just a starter kit — an AI-powered app factory</p>
                    <div className="grid gap-6 sm:grid-cols-3">
                        <Link href="/chat" className="rounded-xl border border-border bg-card p-8 transition-all hover:-translate-y-0.5 hover:shadow-md" data-pan="welcome-diff-ai">
                            <Bot className="mb-4 h-8 w-8 text-primary" />
                            <h3 className="text-lg font-semibold">AI Assistant</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                A built-in AI chat that understands your domain. Ask questions about your data, get help with code, or guide your users. Multi-provider with memory and RAG.
                            </p>
                        </Link>
                        <Link href="/showcase" className="rounded-xl border border-border bg-card p-8 transition-all hover:-translate-y-0.5 hover:shadow-md" data-pan="welcome-diff-modules">
                            <Wand2 className="mb-4 h-8 w-8 text-primary" />
                            <h3 className="text-lg font-semibold">Module System</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Pre-built domain modules (HR, CRM, Fleet) with full CRUD, admin panel, Inertia pages, and tests. Cross-module AI intelligence included.
                            </p>
                        </Link>
                        <div className="rounded-xl border border-border bg-card p-8" data-pan="welcome-diff-scaffold">
                            <Terminal className="mb-4 h-8 w-8 text-primary" />
                            <h3 className="text-lg font-semibold">One-Command Scaffolding</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Run <code className="rounded bg-muted px-1.5 py-0.5 text-xs font-mono">make:module</code> for 18 files or{' '}
                                <code className="rounded bg-muted px-1.5 py-0.5 text-xs font-mono">factory:create</code> with AI analysis. From description to app in minutes.
                            </p>
                        </div>
                    </div>
                </section>

                <div className="h-px w-full bg-gradient-to-r from-transparent via-border to-transparent" />

                {/* Feature grid (condensed) */}
                <section className="mx-auto w-full max-w-5xl space-y-10 px-6 py-20">
                    <div className="text-center">
                        <h2 className="text-2xl font-bold">40+ features, all production-ready</h2>
                        <p className="mt-2 text-muted-foreground">Everything you need across 8 domains</p>
                    </div>
                    {featureGroups.map((group) => (
                        <div key={group.label}>
                            <h3 className="mb-4 text-xs font-semibold tracking-widest text-muted-foreground uppercase">{group.label}</h3>
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                {group.features.map((feature) => (
                                    <div key={feature.title} className="rounded-lg border border-border bg-card p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md" data-pan={feature.dataPan}>
                                        <feature.icon className="mb-2 size-4 text-primary" />
                                        <h4 className="text-sm font-semibold">{feature.title}</h4>
                                        <p className="mt-1 text-xs leading-relaxed text-muted-foreground">{feature.description}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </section>

                <div className="h-px w-full bg-gradient-to-r from-transparent via-border to-transparent" />

                {/* Pricing */}
                <section className="mx-auto w-full max-w-5xl px-6 py-20">
                    <h2 className="mb-2 text-center text-2xl font-bold">Simple pricing</h2>
                    <p className="mb-12 text-center text-muted-foreground">Start building today. Scale when you're ready.</p>
                    <div className="grid gap-6 sm:grid-cols-3">
                        {pricingTiers.map((tier) => (
                            <div
                                key={tier.name}
                                className={`relative rounded-xl border p-6 ${tier.highlighted ? 'border-primary shadow-lg ring-1 ring-primary' : 'border-border bg-card'}`}
                                data-pan={`welcome-pricing-${tier.name.toLowerCase()}`}
                            >
                                {tier.highlighted && (
                                    <span className="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-primary px-3 py-0.5 text-xs font-medium text-primary-foreground">
                                        Most popular
                                    </span>
                                )}
                                <h3 className="text-lg font-semibold">{tier.name}</h3>
                                <p className="text-sm text-muted-foreground">{tier.description}</p>
                                <div className="mt-4">
                                    <span className="text-3xl font-bold">{tier.price}</span>
                                    <span className="text-muted-foreground">{tier.period}</span>
                                </div>
                                <ul className="mt-6 space-y-2">
                                    {tier.features.map((f) => (
                                        <li key={f} className="flex items-center gap-2 text-sm">
                                            <Check className="h-4 w-4 text-green-500" />
                                            {f}
                                        </li>
                                    ))}
                                </ul>
                                <Link
                                    href={tier.name === 'Enterprise' ? '/contact' : register()}
                                    data-pan={`welcome-pricing-${tier.name.toLowerCase()}-cta`}
                                    className={`mt-6 block rounded-md px-4 py-2 text-center text-sm font-medium transition-colors ${
                                        tier.highlighted
                                            ? 'bg-foreground text-background hover:opacity-90'
                                            : 'border border-border hover:bg-accent'
                                    }`}
                                >
                                    {tier.cta}
                                </Link>
                            </div>
                        ))}
                    </div>
                </section>

                <div className="h-px w-full bg-gradient-to-r from-transparent via-border to-transparent" />

                {/* Final CTA */}
                <section className="mx-auto w-full max-w-5xl px-6 py-20 text-center">
                    <h2 className="text-2xl font-bold">Ready to build your next app?</h2>
                    <p className="mt-2 text-muted-foreground">Describe your idea and let the AI Factory do the rest.</p>
                    <div className="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <Link
                            href="/wizard"
                            data-pan="welcome-cta-wizard"
                            className="rounded-md bg-foreground px-8 py-3 text-sm font-semibold text-background transition-colors hover:opacity-90"
                        >
                            <Wand2 className="mr-2 inline h-4 w-4" />
                            Open the Wizard
                        </Link>
                        <div className="rounded-lg bg-muted px-4 py-2.5 font-mono text-xs text-muted-foreground" data-pan="welcome-cta-cli">
                            <Terminal className="mr-2 inline h-3.5 w-3.5" />
                            php artisan factory:create &quot;An HR system for a logistics company&quot;
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-border/60 py-6 text-center text-sm text-muted-foreground">
                    <Link href={legalTerms().url} className="hover:text-foreground hover:underline">
                        Terms of Service
                    </Link>
                    {' · '}
                    <Link href={legalPrivacy().url} className="hover:text-foreground hover:underline">
                        Privacy Policy
                    </Link>
                </footer>
            </div>
        </>
    );
}

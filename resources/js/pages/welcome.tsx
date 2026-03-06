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
    Building2,
    CreditCard,
    Lock,
    MessageCircle,
    Shield,
} from 'lucide-react';

const features = [
    {
        icon: Building2,
        title: 'Organizations & Teams',
        description:
            'Multi-tenant organization management with roles, invitations, and per-org settings.',
        dataPan: 'welcome-feature-orgs',
    },
    {
        icon: CreditCard,
        title: 'Subscription Billing',
        description:
            'Ready-made billing with Stripe, Paddle, and Lemon Squeezy. Plans, trials, and credits built in.',
        dataPan: 'welcome-feature-billing',
    },
    {
        icon: MessageCircle,
        title: 'AI Chat',
        description:
            'Built-in AI chat with conversation memory, streaming responses, and multi-provider support.',
        dataPan: 'welcome-feature-ai',
    },
    {
        icon: Shield,
        title: 'Roles & Permissions',
        description:
            'Granular RBAC per organization. Super-admin, admin, and user roles with fine-grained permissions.',
        dataPan: 'welcome-feature-rbac',
    },
    {
        icon: Lock,
        title: 'Two-Factor Auth',
        description:
            'TOTP authenticator app support with recovery codes and seamless Fortify integration.',
        dataPan: 'welcome-feature-2fa',
    },
    {
        icon: BarChart3,
        title: 'Analytics & Monitoring',
        description:
            'Pan product analytics, Laravel Telescope for debugging, and Horizon for queue monitoring.',
        dataPan: 'welcome-feature-analytics',
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
                                    className="rounded-md border border-border px-4 py-1.5 text-sm font-medium transition-colors hover:bg-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    {flags.blog && (
                                        <Link
                                            href={blogIndex().url}
                                            data-pan="welcome-blog"
                                            className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        >
                                            Blog
                                        </Link>
                                    )}
                                    {flags.changelog && (
                                        <Link
                                            href={changelogIndex().url}
                                            data-pan="welcome-changelog"
                                            className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        >
                                            Changelog
                                        </Link>
                                    )}
                                    {flags.help && (
                                        <Link
                                            href={helpIndex().url}
                                            data-pan="welcome-help"
                                            className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        >
                                            Help
                                        </Link>
                                    )}
                                    {flags.contact && (
                                        <Link
                                            href={contactCreate().url}
                                            data-pan="welcome-contact"
                                            className="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        >
                                            Contact
                                        </Link>
                                    )}
                                    <Link
                                        href={login()}
                                        data-pan="welcome-log-in"
                                        className="rounded-md px-4 py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    >
                                        Log in
                                    </Link>
                                    {flags.registration && (
                                        <Link
                                            href={register()}
                                            data-pan="welcome-register"
                                            className="rounded-md border border-border bg-foreground px-4 py-1.5 text-sm font-medium text-background transition-colors hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
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
                        multi-tenancy, billing, AI chat, auth, and more — built
                        on Laravel & React.
                    </p>
                    <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                        {flags.registration && (
                            <Link
                                href={register()}
                                data-pan="welcome-register"
                                className="rounded-md bg-foreground px-6 py-2.5 text-sm font-semibold text-background transition-colors hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            >
                                Get started →
                            </Link>
                        )}
                        <Link
                            href={login()}
                            data-pan="welcome-log-in"
                            className="rounded-md border border-border px-6 py-2.5 text-sm font-medium transition-colors hover:bg-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            Log in
                        </Link>
                    </div>
                </section>

                <div className="h-px w-full bg-gradient-to-r from-transparent via-border to-transparent" />

                {/* Features Grid */}
                <section className="mx-auto w-full max-w-5xl px-6 pb-24">
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {features.map((feature) => (
                            <div
                                key={feature.title}
                                className="cursor-pointer rounded-xl border border-border bg-card p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                                data-pan={feature.dataPan}
                            >
                                <feature.icon className="mb-3 size-6 text-primary" />
                                <h3 className="font-semibold">
                                    {feature.title}
                                </h3>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {feature.description}
                                </p>
                            </div>
                        ))}
                    </div>
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

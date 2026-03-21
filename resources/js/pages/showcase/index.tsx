import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    Bot,
    CreditCard,
    Database,
    Flag,
    LayoutDashboard,
    MessageSquare,
    Settings,
    Shield,
    Table2,
    Users,
    Workflow,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Showcase', href: '/showcase' }];

type Feature = {
    title: string;
    description: string;
    href: string;
    icon: React.ComponentType<{ className?: string }>;
    tag?: string;
};

const features: Feature[] = [
    {
        title: 'AI Chat Assistant',
        description: 'Conversational AI with memory, RAG, and semantic search. Built on Laravel AI SDK with agent architecture.',
        href: '/chat',
        icon: Bot,
        tag: 'AI-Native',
    },
    {
        title: 'Multi-Tenancy',
        description: 'Organization-based isolation with domain routing, per-org settings, and cross-org sharing.',
        href: '/organizations',
        icon: Users,
    },
    {
        title: 'Billing & Subscriptions',
        description: 'Stripe, Paddle, and Lemon Squeezy. Seat-based billing, credits, invoicing, and dunning.',
        href: '/billing',
        icon: CreditCard,
    },
    {
        title: 'Server-Side DataTables',
        description: 'Full-featured data tables with sorting, filtering, search, exports, and quick views.',
        href: '/users',
        icon: Table2,
    },
    {
        title: 'Admin Panel',
        description: 'Filament v5 admin with 20+ resources, settings management, and role-based access.',
        href: '/admin',
        icon: LayoutDashboard,
    },
    {
        title: 'Durable Workflows',
        description: 'Long-running sagas and approval chains with Waterline monitoring UI.',
        href: '/waterline',
        icon: Workflow,
    },
    {
        title: 'Feature Flags',
        description: 'Laravel Pennant for gradual rollouts, A/B testing, and per-org feature toggling.',
        href: '/settings/features',
        icon: Flag,
    },
    {
        title: 'Roles & Permissions',
        description: 'Spatie permissions with team mode, organization-scoped roles, and JSON-driven sync.',
        href: '/settings/roles',
        icon: Shield,
    },
    {
        title: 'Real-Time Broadcasting',
        description: 'Laravel Reverb + Echo for WebSocket events, presence channels, and live updates.',
        href: '/notifications',
        icon: MessageSquare,
    },
    {
        title: 'DB-Backed Settings',
        description: '29 settings classes with config overlay, per-org overrides, and Filament management.',
        href: '/admin',
        icon: Settings,
    },
    {
        title: 'Search & Indexing',
        description: 'Laravel Scout with Typesense for full-text search across all models.',
        href: '/users',
        icon: Database,
    },
];

export default function ShowcaseIndex() {
    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Feature Showcase" />

            <div className="px-6 py-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold tracking-tight">Feature Showcase</h1>
                    <p className="text-muted-foreground mt-2 text-lg">
                        Everything you need to build corporate-grade AI-native applications.
                        Each feature is production-ready and fully integrated.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {features.map((feature) => (
                        <Link
                            key={feature.title}
                            href={feature.href}
                            className="group relative rounded-lg border p-6 transition-colors hover:border-foreground/25 hover:bg-accent/50"
                        >
                            <div className="flex items-start gap-4">
                                <div className="bg-primary/10 text-primary rounded-lg p-2.5">
                                    <feature.icon className="h-5 w-5" />
                                </div>
                                <div className="flex-1">
                                    <div className="flex items-center gap-2">
                                        <h3 className="font-semibold">{feature.title}</h3>
                                        {feature.tag && (
                                            <span className="bg-primary/10 text-primary rounded-full px-2 py-0.5 text-xs font-medium">
                                                {feature.tag}
                                            </span>
                                        )}
                                    </div>
                                    <p className="text-muted-foreground mt-1 text-sm">{feature.description}</p>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>

                <div className="mt-12 rounded-lg border border-dashed p-8 text-center">
                    <h2 className="text-xl font-semibold">Build Your Own Module</h2>
                    <p className="text-muted-foreground mt-2">
                        Scaffold a complete module with one command — model, actions, controller, pages, tests, and admin resource.
                    </p>
                    <code className="bg-muted mt-4 inline-block rounded-md px-4 py-2 font-mono text-sm">
                        php artisan make:module YourModel
                    </code>
                </div>
            </div>
        </AppSidebarLayout>
    );
}

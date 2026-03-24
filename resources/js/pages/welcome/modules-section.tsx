import { Link } from '@inertiajs/react';
import {
    BarChart3,
    BookOpen,
    CreditCard,
    FileText,
    Gamepad2,
    HelpCircle,
    Layout,
    LineChart,
    Mail,
    Megaphone,
    TrendingUp,
    Truck,
    Users,
    Workflow,
} from 'lucide-react';

const modules = [
    { icon: Users, name: 'HR', title: 'Human Resources', description: 'Employees, departments, leave requests, and approvals.', dataPan: 'welcome-module-hr' },
    { icon: TrendingUp, name: 'CRM', title: 'CRM', description: 'Contacts, deals, pipelines, and activity tracking.', dataPan: 'welcome-module-crm' },
    { icon: CreditCard, name: 'Billing', title: 'Billing', description: 'Stripe, Paddle, Lemon Squeezy, credits, and invoices.', dataPan: 'welcome-module-billing' },
    { icon: FileText, name: 'Blog', title: 'Blog', description: 'Posts, categories, tags, media, and SEO.', dataPan: 'welcome-module-blog' },
    { icon: Layout, name: 'PageBuilder', title: 'Page Builder', description: 'Drag-and-drop pages with Puck and custom blocks.', dataPan: 'welcome-module-page-builder' },
    { icon: BarChart3, name: 'Dashboards', title: 'Dashboards', description: 'Custom analytics dashboards with chart widgets.', dataPan: 'welcome-module-dashboards' },
    { icon: LineChart, name: 'Reports', title: 'Reports', description: 'Generate, schedule, and export PDF/CSV reports.', dataPan: 'welcome-module-reports' },
    { icon: BookOpen, name: 'Changelog', title: 'Changelog', description: 'Public release notes with versioning and types.', dataPan: 'welcome-module-changelog' },
    { icon: HelpCircle, name: 'Help', title: 'Help Center', description: 'Knowledge base articles with search and ratings.', dataPan: 'welcome-module-help' },
    { icon: Megaphone, name: 'Announcements', title: 'Announcements', description: 'In-app banners with targeting and scheduling.', dataPan: 'welcome-module-announcements' },
    { icon: Mail, name: 'Contact', title: 'Contact', description: 'Contact form with spam protection and routing.', dataPan: 'welcome-module-contact' },
    { icon: Gamepad2, name: 'Gamification', title: 'Gamification', description: 'Levels, achievements, and engagement rewards.', dataPan: 'welcome-module-gamification' },
    { icon: Workflow, name: 'Workflows', title: 'Workflows', description: 'Durable long-running workflows with Waterline UI.', dataPan: 'welcome-module-workflows' },
];

export function ModulesSection() {
    return (
        <section className="mx-auto w-full max-w-5xl px-6 py-16">
            <span className="mb-6 block font-mono text-[11px] font-medium uppercase tracking-[0.06em] text-primary">// 13 MODULES</span>
            <h2 className="mb-2 text-2xl font-bold tracking-tight" style={{ letterSpacing: '-0.025em' }}>Pre-built Domain Modules</h2>
            <p className="mb-10 text-sm text-muted-foreground">Each module includes models, controllers, pages, tests, and Filament admin resources</p>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {modules.map((mod) => (
                    <Link
                        key={mod.name}
                        href="/showcase"
                        className="group rounded-lg border border-border bg-card p-5 transition-colors duration-100 hover:bg-accent"
                        data-pan={mod.dataPan}
                    >
                        <mod.icon className="mb-2 h-4 w-4 text-primary" />
                        <h3 className="text-sm font-semibold tracking-tight">{mod.title}</h3>
                        <p className="mt-1 text-xs leading-relaxed text-muted-foreground">{mod.description}</p>
                    </Link>
                ))}
            </div>
            <p className="mt-8 text-sm text-muted-foreground">
                Or create your own:{' '}
                <code className="rounded bg-muted px-2 py-1 font-mono text-xs">php artisan make:module YourModel</code>
                {' '}— scaffolds 18 files in one command
            </p>
        </section>
    );
}

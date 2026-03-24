import { Link } from '@inertiajs/react';
import { TrendingUp, Truck, Users } from 'lucide-react';

export function ModulesSection() {
    return (
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
    );
}

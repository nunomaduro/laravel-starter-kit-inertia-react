import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    XAxis,
    YAxis,
} from 'recharts';
import { create as contactCreate } from '@/routes/contact';
import { exportPdf } from '@/routes/profile';
import { edit as editProfile } from '@/routes/user-profile';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    FileText,
    LifeBuoy,
    Mail,
    Settings,
    UserPen,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    const { auth, features } = usePage<SharedData>().props;
    const f = features ?? {};
    const showPdfExport = f.profile_pdf_export ?? false;
    const showApiDocs = f.scramble_api_docs ?? false;
    const showContact = f.contact ?? false;
    const isSuperAdmin = auth.roles?.includes('super-admin') ?? false;
    const canAccessAdmin =
        (auth.permissions?.includes('access admin panel') ?? false) ||
        auth.can_bypass === true;

    const quickActions = [
        {
            label: 'Edit profile',
            href: editProfile(),
            icon: UserPen,
            show: true,
            dataPan: 'dashboard-quick-edit-profile',
        },
        {
            label: 'Settings',
            href: '/settings',
            icon: Settings,
            show: true,
            dataPan: 'dashboard-quick-settings',
        },
        {
            label: 'Export profile (PDF)',
            href: exportPdf().url,
            icon: FileText,
            show: showPdfExport,
            external: true,
            dataPan: 'dashboard-quick-export-pdf',
        },
        {
            label: 'Contact support',
            href: contactCreate().url,
            icon: LifeBuoy,
            show: showContact,
            dataPan: 'dashboard-quick-contact',
        },
        {
            label: 'Email templates',
            href: '/admin/mail-templates',
            icon: Mail,
            show: isSuperAdmin,
            external: true,
            dataPan: 'dashboard-quick-email-templates',
        },
        {
            label: 'Product analytics',
            href: '/admin/analytics/product',
            icon: BarChart3,
            show: canAccessAdmin,
            external: true,
            dataPan: 'dashboard-quick-product-analytics',
        },
    ].filter((a) => a.show);

    const chartData = [
        { name: 'Mon', value: 12 },
        { name: 'Tue', value: 19 },
        { name: 'Wed', value: 15 },
        { name: 'Thu', value: 22 },
        { name: 'Fri', value: 18 },
        { name: 'Sat', value: 25 },
        { name: 'Sun', value: 20 },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2 className="text-lg font-medium">
                        Welcome back, {auth.user.name}
                    </h2>
                    <div className="flex flex-wrap items-center gap-2">
                        {showApiDocs && (
                            <Button variant="outline" size="sm" asChild>
                                <a
                                    href="/docs/api"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    API documentation
                                </a>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {quickActions.map((action) => (
                        <Button
                            key={action.label}
                            variant="outline"
                            className="h-auto flex-col items-center gap-2 py-6"
                            asChild
                            data-pan={action.dataPan}
                        >
                            {action.external ? (
                                <a
                                    href={
                                        typeof action.href === 'string'
                                            ? action.href
                                            : action.href.url
                                    }
                                >
                                    <action.icon className="size-5 text-muted-foreground" />
                                    <span className="text-sm">
                                        {action.label}
                                    </span>
                                </a>
                            ) : (
                                <Link href={action.href}>
                                    <action.icon className="size-5 text-muted-foreground" />
                                    <span className="text-sm">
                                        {action.label}
                                    </span>
                                </Link>
                            )}
                        </Button>
                    ))}
                </div>

                <div
                    className="rounded-lg border bg-card p-4"
                    data-pan="dashboard-chart"
                >
                    <h3 className="mb-2 font-medium">Activity (sample)</h3>
                    <div className="h-[200px] w-full">
                        <ResponsiveContainer width="100%" height={200} minHeight={200}>
                        <AreaChart data={chartData}>
                            <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                            <XAxis dataKey="name" className="text-xs" />
                            <YAxis className="text-xs" />
                            <Area
                                type="monotone"
                                dataKey="value"
                                stroke="hsl(var(--primary))"
                                fill="hsl(var(--primary))"
                                fillOpacity={0.3}
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                    </div>
                </div>

                {canAccessAdmin && (
                    <div className="rounded-lg border bg-card p-6">
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 className="font-medium">
                                    Product analytics
                                </h3>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    App-wide impressions, hovers, and clicks for
                                    tracked UI elements. View full table and
                                    stats in the admin panel.
                                </p>
                            </div>
                            <Button variant="outline" size="sm" asChild>
                                <a
                                    href="/admin/analytics/product"
                                    data-pan="dashboard-card-view-analytics"
                                >
                                    View analytics
                                </a>
                            </Button>
                        </div>
                    </div>
                )}

                <div className="rounded-lg border bg-card p-6 text-sm text-muted-foreground">
                    <p>
                        This is your dashboard. Use the sidebar to navigate to
                        different sections, or the quick actions above to get
                        started.
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}

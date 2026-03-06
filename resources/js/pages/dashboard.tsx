import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { create as contactCreate } from '@/routes/contact';
import { exportPdf } from '@/routes/profile';
import { edit as editProfile } from '@/routes/user-profile';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    BarChart3,
    Bug,
    FileText,
    GitBranch,
    LifeBuoy,
    Mail,
    UserPen,
    Users,
} from 'lucide-react';
import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    XAxis,
    YAxis,
} from 'recharts';

const ADMIN = {
    root: '/admin',
    mailTemplates: '/admin/mail-templates',
    analytics: '/admin/analytics/product',
    contactSubmissions: '/admin/contact-submissions',
} as const;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface WeeklyStat {
    name: string;
    value: number;
}

interface DashboardProps {
    usersCount?: number;
    orgsCount?: number;
    contactSubmissionsCount?: number;
    weeklyStats?: WeeklyStat[];
    usersGrowthPercent?: number | null;
    orgsGrowthPercent?: number | null;
}

function getGreeting(): string {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 18) return 'Good afternoon';
    return 'Good evening';
}

export default function Dashboard() {
    const { auth, features } = usePage<SharedData>().props;
    const props = usePage<SharedData & DashboardProps>().props;

    const f = features ?? {};
    const showPdfExport = f.profile_pdf_export ?? false;
    const showApiDocs = f.scramble_api_docs ?? false;
    const showContact = f.contact ?? false;
    const isSuperAdmin = auth.roles?.includes('super-admin') ?? false;
    const canAccessAdmin =
        (auth.permissions?.includes('access admin panel') ?? false) ||
        auth.can_bypass === true;

    const weeklyStats: WeeklyStat[] = props.weeklyStats ?? [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h2 className="text-xl font-semibold tracking-tight">
                        {getGreeting()}, {auth.user.name}
                    </h2>
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

                {isSuperAdmin ? (
                    <SuperAdminDashboard
                        usersCount={props.usersCount}
                        orgsCount={props.orgsCount}
                        contactSubmissionsCount={props.contactSubmissionsCount}
                        weeklyStats={weeklyStats}
                        showContact={showContact}
                        usersGrowthPercent={props.usersGrowthPercent}
                        orgsGrowthPercent={props.orgsGrowthPercent}
                    />
                ) : (
                    <UserDashboard
                        showPdfExport={showPdfExport}
                        showContact={showContact}
                        canAccessAdmin={canAccessAdmin}
                        weeklyStats={weeklyStats}
                    />
                )}
            </div>
        </AppLayout>
    );
}

function StatCard({
    label,
    value,
    href,
    icon: Icon,
    dataPan,
    trend,
}: {
    label: string;
    value: number | string | undefined;
    href: string;
    icon: React.FC<{ className?: string }>;
    dataPan: string;
    trend?: { value: number; direction: 'up' | 'down' } | null;
}) {
    return (
        <a
            href={href}
            className="flex flex-col gap-1 rounded-xl border bg-card p-6 transition-colors hover:bg-accent/50"
            data-pan={dataPan}
        >
            <div className="flex items-center gap-2 text-muted-foreground">
                <Icon className="size-4" />
                <span className="text-sm">{label}</span>
            </div>
            <div className="flex items-end justify-between gap-2">
                <p className="text-2xl font-semibold">
                    {value !== undefined ? (
                        value
                    ) : (
                        <Skeleton className="h-8 w-16" />
                    )}
                </p>
                {trend != null && (
                    <span
                        className={
                            trend.direction === 'up'
                                ? 'mb-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400'
                                : 'mb-0.5 text-xs font-medium text-destructive'
                        }
                    >
                        {trend.direction === 'up' ? '↑' : '↓'}{' '}
                        {Math.abs(trend.value)}%
                    </span>
                )}
            </div>
        </a>
    );
}

function ActivityChart({ data }: { data: WeeklyStat[] }) {
    return (
        <div className="rounded-xl border bg-card p-6" data-pan="dashboard-chart">
            <h3 className="mb-4 font-medium">Activity this week</h3>
            {data.length === 0 ? (
                <div className="h-[200px] w-full">
                    <div className="flex h-full items-center justify-center">
                        <Skeleton className="h-[180px] w-full rounded" />
                    </div>
                </div>
            ) : (
                <div className="text-primary h-[200px] w-full">
                    <ResponsiveContainer width="100%" height={200} minHeight={200}>
                        <AreaChart data={data}>
                            <CartesianGrid
                                strokeDasharray="3 3"
                                className="stroke-muted"
                            />
                            <XAxis dataKey="name" className="text-xs" />
                            <YAxis className="text-xs" allowDecimals={false} />
                            <Area
                                type="monotone"
                                dataKey="value"
                                stroke="currentColor"
                                fill="currentColor"
                                fillOpacity={0.2}
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                </div>
            )}
        </div>
    );
}

function SuperAdminDashboard({
    usersCount,
    orgsCount,
    contactSubmissionsCount,
    weeklyStats,
    showContact,
    usersGrowthPercent,
    orgsGrowthPercent,
}: {
    usersCount?: number;
    orgsCount?: number;
    contactSubmissionsCount?: number;
    weeklyStats: WeeklyStat[];
    showContact: boolean;
    usersGrowthPercent?: number | null;
    orgsGrowthPercent?: number | null;
}) {
    const toTrend = (
        pct: number | null | undefined,
    ): { value: number; direction: 'up' | 'down' } | null => {
        if (pct == null) return null;
        return { value: Math.abs(pct), direction: pct >= 0 ? 'up' : 'down' };
    };

    return (
        <>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    label="Total users"
                    value={usersCount}
                    href={`${ADMIN.root}/users`}
                    icon={Users}
                    dataPan="dashboard-admin-users"
                    trend={toTrend(usersGrowthPercent)}
                />
                <StatCard
                    label="Organizations"
                    value={orgsCount}
                    href={`${ADMIN.root}/organizations`}
                    icon={BarChart3}
                    dataPan="dashboard-admin-orgs"
                    trend={toTrend(orgsGrowthPercent)}
                />
                {showContact && (
                    <StatCard
                        label="Contact submissions"
                        value={contactSubmissionsCount}
                        href={ADMIN.contactSubmissions}
                        icon={Mail}
                        dataPan="dashboard-admin-contact"
                    />
                )}
            </div>

            <ActivityChart data={weeklyStats} />

            <div className="rounded-lg border bg-card p-6">
                <h3 className="mb-4 font-medium">Admin tools</h3>
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {[
                        {
                            label: 'Horizon (queues)',
                            href: '/horizon',
                            icon: Activity,
                            dataPan: 'dashboard-quick-horizon',
                        },
                        {
                            label: 'Waterline (workflows)',
                            href: '/waterline',
                            icon: GitBranch,
                            dataPan: 'dashboard-quick-waterline',
                        },
                        {
                            label: 'Telescope (debug)',
                            href: '/telescope',
                            icon: Bug,
                            dataPan: 'dashboard-quick-telescope',
                        },
                        {
                            label: 'Email templates',
                            href: ADMIN.mailTemplates,
                            icon: Mail,
                            dataPan: 'dashboard-quick-email-templates',
                        },
                        {
                            label: 'Product analytics',
                            href: ADMIN.analytics,
                            icon: BarChart3,
                            dataPan: 'dashboard-quick-product-analytics',
                        },
                    ].map((tool) => (
                        <Button
                            key={tool.label}
                            variant="outline"
                            className="h-auto justify-start gap-2 py-3"
                            asChild
                            data-pan={tool.dataPan}
                        >
                            <a href={tool.href}>
                                <tool.icon className="size-4 text-muted-foreground" />
                                <span className="text-sm">{tool.label}</span>
                            </a>
                        </Button>
                    ))}
                </div>
            </div>
        </>
    );
}

function UserDashboard({
    showPdfExport,
    showContact,
    canAccessAdmin,
    weeklyStats,
}: {
    showPdfExport: boolean;
    showContact: boolean;
    canAccessAdmin: boolean;
    weeklyStats: WeeklyStat[];
}) {
    const quickActions = [
        {
            label: 'Edit profile',
            href: editProfile().url,
            icon: UserPen,
            show: true,
            external: false,
            dataPan: 'dashboard-quick-edit-profile',
        },
        {
            label: 'Settings',
            href: editProfile().url,
            icon: UserPen,
            show: true,
            external: false,
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
            external: false,
            dataPan: 'dashboard-quick-contact',
        },
    ].filter((a) => a.show);

    return (
        <>
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
                            <a href={action.href}>
                                <action.icon className="size-5 text-muted-foreground" />
                                <span className="text-sm">{action.label}</span>
                            </a>
                        ) : (
                            <Link href={action.href}>
                                <action.icon className="size-5 text-muted-foreground" />
                                <span className="text-sm">{action.label}</span>
                            </Link>
                        )}
                    </Button>
                ))}
            </div>

            <ActivityChart data={weeklyStats} />

            {canAccessAdmin && (
                <div className="rounded-lg border bg-card p-6">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 className="font-medium">Product analytics</h3>
                            <p className="mt-1 text-sm text-muted-foreground">
                                App-wide impressions, hovers, and clicks for
                                tracked UI elements.
                            </p>
                        </div>
                        <Button variant="outline" size="sm" asChild>
                            <a
                                href={ADMIN.analytics}
                                data-pan="dashboard-card-view-analytics"
                            >
                                View analytics
                            </a>
                        </Button>
                    </div>
                </div>
            )}
        </>
    );
}

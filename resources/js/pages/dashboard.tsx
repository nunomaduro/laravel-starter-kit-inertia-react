import type { ActivityEntry } from '@/components/composed/activity-log';
import type { WeeklyStat } from '@/components/dashboard/activity-chart';
import { SuperAdminDashboard } from '@/components/dashboard/super-admin-dashboard';
import { UserDashboard } from '@/components/dashboard/user-dashboard';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardProps {
    usersCount?: number;
    orgsCount?: number;
    contactSubmissionsCount?: number;
    weeklyStats?: WeeklyStat[];
    recentActivity?: ActivityEntry[];
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
                    <h2 className="text-xl font-mono font-semibold tracking-tight">
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
                        recentActivity={props.recentActivity ?? []}
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

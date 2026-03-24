import { ActivityLog, type ActivityEntry } from '@/components/composed/activity-log';
import { Button } from '@/components/ui/button';
import {
    Activity,
    BarChart3,
    GitBranch,
    Heart,
    Mail,
    Users,
} from 'lucide-react';
import { ActivityChart, type WeeklyStat } from './activity-chart';
import { StatCard } from './stat-card';

const SYSTEM = {
    root: '/system',
    mailTemplates: '/system/mail-templates',
    analytics: '/system/analytics/product',
    contactSubmissions: '/system/contact-submissions',
} as const;

interface SuperAdminDashboardProps {
    usersCount?: number;
    orgsCount?: number;
    contactSubmissionsCount?: number;
    weeklyStats: WeeklyStat[];
    recentActivity: ActivityEntry[];
    showContact: boolean;
    usersGrowthPercent?: number | null;
    orgsGrowthPercent?: number | null;
}

export function SuperAdminDashboard({
    usersCount,
    orgsCount,
    contactSubmissionsCount,
    weeklyStats,
    recentActivity,
    showContact,
    usersGrowthPercent,
    orgsGrowthPercent,
}: SuperAdminDashboardProps) {
    const toTrend = (
        pct: number | null | undefined,
    ): { value: number; direction: 'up' | 'down' } | null => {
        if (pct == null) return null;
        return { value: Math.abs(pct), direction: pct >= 0 ? 'up' : 'down' };
    };

    return (
        <>
            <div
                className={`grid gap-4 sm:grid-cols-2 ${showContact ? 'lg:grid-cols-3' : 'lg:grid-cols-2'}`}
            >
                <StatCard
                    label="Total users"
                    value={usersCount}
                    href={`${SYSTEM.root}/users`}
                    icon={Users}
                    dataPan="dashboard-admin-users"
                    trend={toTrend(usersGrowthPercent)}
                />
                <StatCard
                    label="Organizations"
                    value={orgsCount}
                    href={`${SYSTEM.root}/organizations`}
                    icon={BarChart3}
                    dataPan="dashboard-admin-orgs"
                    trend={toTrend(orgsGrowthPercent)}
                />
                {showContact && (
                    <StatCard
                        label="Contact submissions"
                        value={contactSubmissionsCount}
                        href={SYSTEM.contactSubmissions}
                        icon={Mail}
                        dataPan="dashboard-admin-contact"
                        trend={null}
                    />
                )}
            </div>

            <div className="grid gap-4 lg:grid-cols-2">
                <ActivityChart data={weeklyStats} />
                <div className="rounded-xl border bg-card p-6" data-pan="dashboard-activity-feed">
                    <h3 className="mb-4 font-medium">Recent activity</h3>
                    {recentActivity.length > 0 ? (
                        <ActivityLog entries={recentActivity} maxHeight={232} />
                    ) : (
                        <div className="flex h-[200px] items-center justify-center text-sm text-muted-foreground">
                            No recent activity
                        </div>
                    )}
                </div>
            </div>

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
                            label: 'Pulse (monitoring)',
                            href: '/pulse',
                            icon: Heart,
                            dataPan: 'dashboard-quick-pulse',
                        },
                        {
                            label: 'Email templates',
                            href: SYSTEM.mailTemplates,
                            icon: Mail,
                            dataPan: 'dashboard-quick-email-templates',
                        },
                        {
                            label: 'Product analytics',
                            href: SYSTEM.analytics,
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

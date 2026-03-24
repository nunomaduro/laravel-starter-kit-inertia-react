import { OnboardingCard } from '@/components/onboarding-card';
import { Button } from '@/components/ui/button';
import { create as contactCreate } from '@/routes/contact';
import { exportPdf } from '@/routes/profile';
import { edit as editProfile } from '@/routes/user-profile';
import { Link } from '@inertiajs/react';
import {
    FileText,
    LifeBuoy,
    UserPen,
} from 'lucide-react';
import { ActivityChart, type WeeklyStat } from './activity-chart';

const ADMIN = {
    analytics: '/admin/analytics/product',
} as const;

interface UserDashboardProps {
    showPdfExport: boolean;
    showContact: boolean;
    canAccessAdmin: boolean;
    weeklyStats: WeeklyStat[];
}

export function UserDashboard({
    showPdfExport,
    showContact,
    canAccessAdmin,
    weeklyStats,
}: UserDashboardProps) {
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
            <OnboardingCard />

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

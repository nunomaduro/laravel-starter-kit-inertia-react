import DashboardBuilderController from '@/actions/Modules/Dashboards/Http/Controllers/DashboardBuilderController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Render } from '@measured/puck';
import { Head } from '@inertiajs/react';
import { useMemo } from 'react';
import { createDashboardPuckConfig } from '@/lib/dashboard-puck-config';

interface Props {
    dashboard: {
        id: number;
        name: string;
        puck_json: {
            root: Record<string, unknown>;
            content: Record<string, unknown>[];
        };
        is_default: boolean;
        refresh_interval: number | null;
    };
}

export default function DashboardShow({ dashboard: dashboardRecord }: Props) {
    const config = useMemo(
        () => createDashboardPuckConfig([], dashboardRecord.refresh_interval),
        [dashboardRecord.refresh_interval],
    );

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        {
            title: 'Dashboards',
            href: DashboardBuilderController.index().url,
        },
        {
            title: dashboardRecord.name,
            href: DashboardBuilderController.show.url({
                dashboard: dashboardRecord.id,
            }),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={dashboardRecord.name} />
            <div className="p-4">
                {/* @ts-expect-error Puck Render types are loosely typed */}
                <Render config={config} data={dashboardRecord.puck_json} />
            </div>
        </AppLayout>
    );
}

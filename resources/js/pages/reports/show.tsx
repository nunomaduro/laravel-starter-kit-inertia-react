import ReportController from '@/actions/Modules/Reports/Http/Controllers/ReportController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Render } from '@measured/puck';
import { Head } from '@inertiajs/react';
import { useMemo } from 'react';
import { createReportPuckConfig } from '@/lib/report-puck-config';

interface Props {
    report: {
        id: number;
        name: string;
        puck_json: {
            root: Record<string, unknown>;
            content: Record<string, unknown>[];
        };
        output_format: string;
    };
}

export default function ReportShow({ report }: Props) {
    const config = useMemo(() => createReportPuckConfig([]), []);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Reports', href: ReportController.index().url },
        { title: report.name, href: ReportController.show.url({ report: report.id }) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={report.name} />
            <div className="p-4">
                {/* @ts-expect-error Puck Render types are loosely typed */}
                <Render config={config} data={report.puck_json} />
            </div>
        </AppLayout>
    );
}

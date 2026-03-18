import ReportController from '@/actions/Modules/Reports/Http/Controllers/ReportController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { lazy, Suspense, useMemo } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    createReportPuckConfig,
    type DataSourceOption,
} from '@/lib/report-puck-config';

const PuckEditor = lazy(() =>
    import('@measured/puck').then((m) => {
        void import('@measured/puck/puck.css');
        return { default: m.Puck };
    }),
);

interface ReportRecord {
    id: number;
    name: string;
    puck_json: Record<string, unknown>;
    schedule: string | null;
    output_format: string;
}

interface Props {
    report: ReportRecord | null;
    puckJson: {
        root: Record<string, unknown>;
        content: Record<string, unknown>[];
    };
    dataSources?: DataSourceOption[];
}

const emptyPuckData: {
    root: Record<string, unknown>;
    content: Record<string, unknown>[];
} = { root: {}, content: [] };

export default function ReportEdit({
    report,
    puckJson,
    dataSources = [],
}: Props) {
    const isCreate = report === null;
    const initialData = puckJson?.content ? puckJson : emptyPuckData;

    const reportPuckConfig = useMemo(
        () => createReportPuckConfig(dataSources),
        [dataSources],
    );

    const { data, setData, post, put, processing, errors } = useForm({
        name: report?.name ?? '',
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        puck_json: initialData as any,
        schedule: report?.schedule ?? '',
        output_format: report?.output_format ?? 'pdf',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Reports', href: ReportController.index().url },
        {
            title: isCreate ? 'New report' : (report?.name ?? 'Edit'),
            href: isCreate
                ? ReportController.create().url
                : ReportController.edit.url({ report: report!.id }),
        },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (isCreate) {
            post(ReportController.store().url);
        } else {
            put(ReportController.update.url({ report: report!.id }));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head
                title={
                    isCreate ? 'New report' : `Edit: ${report?.name ?? ''}`
                }
            />
            <form
                onSubmit={handleSubmit}
                className="flex h-full flex-1 flex-col gap-6 overflow-hidden p-4"
            >
                <div className="flex flex-wrap items-end gap-4 border-b pb-4">
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Report name"
                            className="w-64"
                        />
                        <InputError message={errors.name} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="output_format">Output format</Label>
                        <Select
                            value={data.output_format}
                            onValueChange={(v) => setData('output_format', v)}
                        >
                            <SelectTrigger className="w-32">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pdf">PDF</SelectItem>
                                <SelectItem value="html">HTML</SelectItem>
                                <SelectItem value="csv">CSV</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError message={errors.output_format} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="schedule">Schedule (cron)</Label>
                        <Input
                            id="schedule"
                            value={data.schedule}
                            onChange={(e) => setData('schedule', e.target.value)}
                            placeholder="e.g. 0 9 * * 1"
                            className="w-48 font-mono text-sm"
                        />
                        <InputError message={errors.schedule} />
                    </div>
                    <div className="flex gap-2">
                        <Button
                            type="submit"
                            disabled={processing}
                            data-pan={
                                isCreate ? undefined : 'reports-edit-save'
                            }
                        >
                            {isCreate ? 'Create' : 'Save'}
                        </Button>
                        {!isCreate && (
                            <Button type="button" variant="outline" asChild>
                                <a href={ReportController.index().url}>
                                    Cancel
                                </a>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="min-h-0 flex-1 rounded-lg border">
                    <Suspense
                        fallback={
                            <div className="flex h-96 items-center justify-center text-muted-foreground">
                                Loading editor…
                            </div>
                        }
                    >
                        <PuckEditor
                            config={reportPuckConfig}
                            data={data.puck_json}
                            onChange={(next) => setData('puck_json', next)}
                            onPublish={(next) => setData('puck_json', next)}
                        />
                    </Suspense>
                </div>
            </form>
        </AppLayout>
    );
}

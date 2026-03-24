import DashboardBuilderController from '@/actions/Modules/Dashboards/Http/Controllers/DashboardBuilderController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { lazy, Suspense, useMemo } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    createDashboardPuckConfig,
    type DataSourceOption,
} from '@/lib/dashboard-puck-config';

const PuckEditor = lazy(() =>
    import('@measured/puck').then((m) => {
        void import('@measured/puck/puck.css');
        return { default: m.Puck };
    }),
);

import { type DashboardRecordEditable as DashboardRecord } from '@/types/content';

interface Props {
    dashboard: DashboardRecord | null;
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

export default function DashboardEdit({
    dashboard: dashboardRecord,
    puckJson,
    dataSources = [],
}: Props) {
    const isCreate = dashboardRecord === null;
    const initialData = puckJson?.content ? puckJson : emptyPuckData;

    const dashboardPuckConfig = useMemo(
        () =>
            createDashboardPuckConfig(
                dataSources,
                dashboardRecord?.refresh_interval,
            ),
        [dataSources, dashboardRecord?.refresh_interval],
    );

    const { data, setData, post, put, processing, errors } = useForm({
        name: dashboardRecord?.name ?? '',
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        puck_json: initialData as any,
        is_default: dashboardRecord?.is_default ?? false,
        refresh_interval: dashboardRecord?.refresh_interval ?? '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        {
            title: 'Dashboards',
            href: DashboardBuilderController.index().url,
        },
        {
            title: isCreate
                ? 'New dashboard'
                : (dashboardRecord?.name ?? 'Edit'),
            href: isCreate
                ? DashboardBuilderController.create().url
                : DashboardBuilderController.edit.url({
                      dashboard: dashboardRecord!.id,
                  }),
        },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (isCreate) {
            post(DashboardBuilderController.store().url);
        } else {
            put(
                DashboardBuilderController.update.url({
                    dashboard: dashboardRecord!.id,
                }),
            );
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head
                title={
                    isCreate
                        ? 'New dashboard'
                        : `Edit: ${dashboardRecord?.name ?? ''}`
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
                            placeholder="Dashboard name"
                            className="w-64"
                        />
                        <InputError message={errors.name} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="refresh_interval">
                            Refresh interval (seconds)
                        </Label>
                        <Input
                            id="refresh_interval"
                            type="number"
                            min={5}
                            max={3600}
                            value={data.refresh_interval}
                            onChange={(e) =>
                                setData(
                                    'refresh_interval',
                                    e.target.value === ''
                                        ? ''
                                        : Number(e.target.value),
                                )
                            }
                            placeholder="e.g. 30"
                            className="w-40"
                        />
                        <InputError message={errors.refresh_interval} />
                    </div>
                    <div className="flex items-center gap-2 pb-1">
                        <Checkbox
                            id="is_default"
                            checked={data.is_default}
                            onCheckedChange={(checked) =>
                                setData('is_default', checked === true)
                            }
                        />
                        <Label htmlFor="is_default" className="text-sm">
                            Default dashboard
                        </Label>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            type="submit"
                            disabled={processing}
                            data-pan={
                                isCreate ? undefined : 'dashboards-edit-save'
                            }
                        >
                            {isCreate ? 'Create' : 'Save'}
                        </Button>
                        {!isCreate && (
                            <Button type="button" variant="outline" asChild>
                                <a
                                    href={
                                        DashboardBuilderController.index().url
                                    }
                                >
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
                            config={dashboardPuckConfig}
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

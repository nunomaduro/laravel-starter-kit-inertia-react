import DashboardBuilderController from '@/actions/Modules/Dashboards/Http/Controllers/DashboardBuilderController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { useAutoAnimate } from '@formkit/auto-animate/react';
import { Form, Head, Link } from '@inertiajs/react';
import { LayoutDashboard, Pencil, Plus, Star, Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';

interface DashboardRecord {
    id: number;
    name: string;
    is_default: boolean;
    refresh_interval: number | null;
    updated_at: string;
}

interface Props {
    dashboards: DashboardRecord[];
}

export default function DashboardsIndex({ dashboards }: Props) {
    const [autoAnimateParent] = useAutoAnimate({ duration: 200 });
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Dashboards', href: DashboardBuilderController.index().url },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboards" />
            <div
                className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
                data-pan="dashboards-index"
            >
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-mono font-semibold tracking-tight">Dashboards</h1>
                    <Button asChild data-pan="dashboards-create">
                        <Link
                            href={
                                DashboardBuilderController.create().url
                            }
                        >
                            <Plus className="mr-2 size-4" />
                            New dashboard
                        </Link>
                    </Button>
                </div>

                {dashboards.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16 text-center">
                        <LayoutDashboard className="size-10 text-muted-foreground" />
                        <p className="mt-2 text-sm font-medium text-muted-foreground">
                            No dashboards yet
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Create a dashboard to get started.
                        </p>
                        <Button
                            asChild
                            className="mt-4"
                            data-pan="dashboards-create"
                        >
                            <Link
                                href={
                                    DashboardBuilderController.create().url
                                }
                            >
                                <Plus className="mr-2 size-4" />
                                Create dashboard
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <ul ref={autoAnimateParent} className="space-y-2">
                        {dashboards.map((dash) => (
                            <li
                                key={dash.id}
                                className="flex items-center justify-between gap-4 rounded-lg border bg-card p-4"
                            >
                                <div className="min-w-0 flex-1">
                                    <Link
                                        href={DashboardBuilderController.edit.url(
                                            {
                                                dashboard: dash.id,
                                            },
                                        )}
                                        className="font-medium text-foreground hover:underline"
                                    >
                                        {dash.name}
                                    </Link>
                                    <p className="text-xs text-muted-foreground">
                                        {dash.is_default && (
                                            <span className="mr-2 inline-flex items-center gap-1 rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-medium text-primary">
                                                <Star className="size-2.5" />
                                                Default
                                            </span>
                                        )}
                                        {dash.refresh_interval && (
                                            <span className="rounded bg-muted px-1.5 py-0.5 text-[10px]">
                                                Refresh:{' '}
                                                {dash.refresh_interval}s
                                            </span>
                                        )}
                                    </p>
                                </div>
                                <div className="flex shrink-0 items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        asChild
                                    >
                                        <Link
                                            href={DashboardBuilderController.show.url(
                                                {
                                                    dashboard: dash.id,
                                                },
                                            )}
                                        >
                                            View
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        asChild
                                    >
                                        <Link
                                            href={DashboardBuilderController.edit.url(
                                                {
                                                    dashboard: dash.id,
                                                },
                                            )}
                                            data-pan="dashboards-edit"
                                        >
                                            <Pencil className="mr-1 size-3.5" />
                                            Edit
                                        </Link>
                                    </Button>
                                    {!dash.is_default && (
                                        <Form
                                            action={DashboardBuilderController.setDefault.url(
                                                {
                                                    dashboard: dash.id,
                                                },
                                            )}
                                            method="post"
                                        >
                                            <Button
                                                type="submit"
                                                variant="ghost"
                                                size="sm"
                                                data-pan="dashboards-set-default"
                                            >
                                                <Star className="size-3.5" />
                                            </Button>
                                        </Form>
                                    )}
                                    <Form
                                        action={DashboardBuilderController.destroy.url(
                                            {
                                                dashboard: dash.id,
                                            },
                                        )}
                                        method="delete"
                                        onSubmit={(e) => {
                                            if (
                                                !confirm(
                                                    'Delete this dashboard? This cannot be undone.',
                                                )
                                            ) {
                                                e.preventDefault();
                                            }
                                        }}
                                    >
                                        <Button
                                            type="submit"
                                            variant="ghost"
                                            size="sm"
                                            data-pan="dashboards-delete"
                                        >
                                            <Trash2 className="size-3.5 text-destructive" />
                                        </Button>
                                    </Form>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AppLayout>
    );
}

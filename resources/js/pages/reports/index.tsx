import ReportController from '@/actions/Modules/Reports/Http/Controllers/ReportController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { useAutoAnimate } from '@formkit/auto-animate/react';
import { Form, Head, Link } from '@inertiajs/react';
import { FileText, Pencil, Plus, Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';

interface ReportRecord {
    id: number;
    name: string;
    output_format: string;
    schedule: string | null;
    updated_at: string;
}

interface Props {
    reports: ReportRecord[];
}

export default function ReportsIndex({ reports }: Props) {
    const [autoAnimateParent] = useAutoAnimate({ duration: 200 });
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Reports', href: ReportController.index().url },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reports" />
            <div
                className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
                data-pan="reports-index"
            >
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Reports</h1>
                    <Button asChild data-pan="reports-create">
                        <Link href={ReportController.create().url}>
                            <Plus className="mr-2 size-4" />
                            New report
                        </Link>
                    </Button>
                </div>

                {reports.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed py-16 text-center">
                        <FileText className="size-10 text-muted-foreground" />
                        <p className="mt-2 text-sm font-medium text-muted-foreground">
                            No reports yet
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            Create a report to get started.
                        </p>
                        <Button
                            asChild
                            className="mt-4"
                            data-pan="reports-create"
                        >
                            <Link href={ReportController.create().url}>
                                <Plus className="mr-2 size-4" />
                                Create report
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <ul ref={autoAnimateParent} className="space-y-2">
                        {reports.map((report) => (
                            <li
                                key={report.id}
                                className="flex items-center justify-between gap-4 rounded-lg border bg-card p-4"
                            >
                                <div className="min-w-0 flex-1">
                                    <Link
                                        href={ReportController.edit.url({
                                            report: report.id,
                                        })}
                                        className="font-medium text-foreground hover:underline"
                                    >
                                        {report.name}
                                    </Link>
                                    <p className="text-xs text-muted-foreground">
                                        {report.output_format.toUpperCase()}
                                        {report.schedule && (
                                            <span className="ml-2 rounded bg-muted px-1.5 py-0.5 text-[10px]">
                                                Scheduled
                                            </span>
                                        )}
                                    </p>
                                </div>
                                <div className="flex shrink-0 items-center gap-2">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link
                                            href={ReportController.show.url({
                                                report: report.id,
                                            })}
                                        >
                                            View
                                        </Link>
                                    </Button>
                                    <Button variant="outline" size="sm" asChild>
                                        <Link
                                            href={ReportController.edit.url({
                                                report: report.id,
                                            })}
                                            data-pan="reports-edit"
                                        >
                                            <Pencil className="mr-1 size-3.5" />
                                            Edit
                                        </Link>
                                    </Button>
                                    <Form
                                        action={ReportController.destroy.url({
                                            report: report.id,
                                        })}
                                        method="delete"
                                        onSubmit={(e) => {
                                            if (
                                                !confirm(
                                                    'Delete this report? This cannot be undone.',
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
                                            data-pan="reports-delete"
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

import ReportController from '@/actions/Modules/Reports/Http/Controllers/ReportController';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Render } from '@measured/puck';
import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { createReportPuckConfig } from '@/lib/report-puck-config';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface ReportOutput {
    id: number;
    format: string;
    size_bytes: number;
    is_scheduled: boolean;
    created_at: string;
}

interface Props {
    report: {
        id: number;
        name: string;
        puck_json: {
            root: Record<string, unknown>;
            content: Record<string, unknown>[];
        };
        output_format: string;
        schedule: string | null;
    };
    outputs: ReportOutput[];
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${units[i]}`;
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleString();
}

export default function ReportShow({ report, outputs }: Props) {
    const config = useMemo(() => createReportPuckConfig([]), []);
    const [exportFormat, setExportFormat] = useState(report.output_format);
    const [exporting, setExporting] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: dashboard().url },
        { title: 'Reports', href: ReportController.index().url },
        { title: report.name, href: ReportController.show.url({ report: report.id }) },
    ];

    const handleExport = () => {
        setExporting(true);
        router.post(
            ReportController.export.url({ report: report.id }),
            { format: exportFormat },
            {
                onFinish: () => setExporting(false),
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={report.name} />
            <div className="flex flex-col gap-6 p-4">
                {/* Export toolbar */}
                <div className="flex flex-wrap items-center gap-4 rounded-lg border bg-muted/30 p-4">
                    <div className="flex items-center gap-2">
                        <span className="text-sm font-medium">Export as:</span>
                        <Select value={exportFormat} onValueChange={setExportFormat}>
                            <SelectTrigger className="w-28">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pdf">PDF</SelectItem>
                                <SelectItem value="html">HTML</SelectItem>
                                <SelectItem value="csv">CSV</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button onClick={handleExport} disabled={exporting} size="sm">
                            {exporting ? 'Exporting…' : 'Export'}
                        </Button>
                    </div>
                    {report.schedule && (
                        <div className="text-sm text-muted-foreground">
                            Scheduled: <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">{report.schedule}</code>
                        </div>
                    )}
                    <Button variant="outline" size="sm" asChild className="ml-auto">
                        <a href={ReportController.edit.url({ report: report.id })}>Edit</a>
                    </Button>
                </div>

                {/* Report content */}
                <div className="rounded-lg border p-4">
                    {/* @ts-expect-error Puck Render types are loosely typed */}
                    <Render config={config} data={report.puck_json} />
                </div>

                {/* Past outputs */}
                {outputs.length > 0 && (
                    <div className="rounded-lg border">
                        <div className="border-b px-4 py-3">
                            <h3 className="text-sm font-semibold">Past exports</h3>
                        </div>
                        <div className="divide-y">
                            {outputs.map((output) => (
                                <div key={output.id} className="flex items-center justify-between px-4 py-3">
                                    <div className="flex items-center gap-3">
                                        <span className="rounded bg-muted px-2 py-0.5 font-mono text-xs uppercase">{output.format}</span>
                                        <span className="text-sm text-muted-foreground">{formatDate(output.created_at)}</span>
                                        <span className="text-xs text-muted-foreground">{formatBytes(output.size_bytes)}</span>
                                        {output.is_scheduled && (
                                            <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-900 dark:text-blue-300">scheduled</span>
                                        )}
                                    </div>
                                    <Button variant="ghost" size="sm" asChild>
                                        <a href={ReportController.downloadOutput.url({ report: report.id, output: output.id })}>
                                            Download
                                        </a>
                                    </Button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

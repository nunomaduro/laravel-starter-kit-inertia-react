import { Head, usePage } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface AuditLogEntry {
    id: number;
    action: string;
    subject_type: string | null;
    subject_id: string | null;
    old_value: Record<string, unknown> | null;
    new_value: Record<string, unknown> | null;
    actor: { name: string; email: string } | null;
    actor_type: string;
    ip_address: string | null;
    created_at: string | null;
}

interface PaginatedLogs {
    data: AuditLogEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface PageProps extends SharedData {
    logs: PaginatedLogs;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Audit Log', href: '/settings/audit-log' },
];

const ACTION_LABELS: Record<string, string> = {
    'theme.saved': 'Theme Saved',
    'theme.reset': 'Theme Reset',
    'logo.uploaded': 'Logo Uploaded',
    'branding.user_controls.changed': 'Branding Controls Changed',
    'feature.toggled': 'Feature Toggled',
    'member.invited': 'Member Invited',
    'member.removed': 'Member Removed',
    'role.created': 'Role Created',
    'role.deleted': 'Role Deleted',
    'system.setting.changed': 'System Setting Changed',
    'slug.changed': 'Workspace URL Changed',
    'domain.added': 'Custom Domain Added',
    'domain.removed': 'Custom Domain Removed',
};

function formatDate(iso: string | null): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleString();
}

export default function AuditLog() {
    const { logs } = usePage<PageProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit Log" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Audit Log"
                        description="A record of settings changes and administrative actions in your organization."
                    />

                    {logs.data.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No audit log entries yet.
                        </p>
                    ) : (
                        <div className="overflow-x-auto rounded-lg border">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b bg-muted/50 text-left text-xs font-medium text-muted-foreground">
                                        <th className="px-4 py-3">Date</th>
                                        <th className="px-4 py-3">Actor</th>
                                        <th className="px-4 py-3">Action</th>
                                        <th className="px-4 py-3">Subject</th>
                                        <th className="px-4 py-3">Changes</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {logs.data.map((entry) => (
                                        <tr
                                            key={entry.id}
                                            className="hover:bg-muted/30"
                                        >
                                            <td className="px-4 py-3 text-xs whitespace-nowrap text-muted-foreground">
                                                {formatDate(entry.created_at)}
                                            </td>
                                            <td className="px-4 py-3">
                                                {entry.actor ? (
                                                    <div>
                                                        <p className="font-medium">
                                                            {entry.actor.name}
                                                        </p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {entry.actor.email}
                                                        </p>
                                                    </div>
                                                ) : (
                                                    <span className="text-muted-foreground">
                                                        System
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span className="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                                                    {ACTION_LABELS[
                                                        entry.action
                                                    ] ?? entry.action}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-xs text-muted-foreground">
                                                {entry.subject_type && (
                                                    <span className="font-medium">
                                                        {entry.subject_type}
                                                    </span>
                                                )}
                                                {entry.subject_id && (
                                                    <span>
                                                        {' '}
                                                        · {entry.subject_id}
                                                    </span>
                                                )}
                                                {!entry.subject_type && '—'}
                                            </td>
                                            <td className="px-4 py-3">
                                                {entry.new_value ? (
                                                    <pre className="max-w-xs overflow-x-auto rounded bg-muted p-1.5 text-[10px] leading-relaxed">
                                                        {JSON.stringify(
                                                            entry.new_value,
                                                            null,
                                                            2,
                                                        )}
                                                    </pre>
                                                ) : (
                                                    '—'
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {logs.last_page > 1 && (
                        <p className="text-xs text-muted-foreground">
                            Page {logs.current_page} of {logs.last_page} ·{' '}
                            {logs.total} total entries
                        </p>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

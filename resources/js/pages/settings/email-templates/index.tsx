import { Head, Link, usePage } from '@inertiajs/react';
import { Mail } from 'lucide-react';

import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit as editEmailTemplate } from '@/routes/settings/email-templates';
import { type BreadcrumbItem, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Email Templates', href: '/settings/email-templates' },
];

interface TemplateItem {
    event_class: string;
    event_name: string;
    description: string;
    is_customized: boolean;
    subject: string | null;
    updated_at: string | null;
}

interface PageProps extends SharedData {
    templates: TemplateItem[];
}

function StatusBadge({ isCustomized }: { isCustomized: boolean }) {
    if (isCustomized) {
        return (
            <span className="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                Customized
            </span>
        );
    }

    return (
        <span className="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium text-muted-foreground">
            Default
        </span>
    );
}

function TemplateCard({ template }: { template: TemplateItem }) {
    return (
        <Link
            href={editEmailTemplate.url({ event: template.event_name })}
            className="block rounded-lg border bg-muted/50 px-4 py-3 transition-colors hover:bg-muted/80"
            data-pan="email-template-edit"
        >
            <div className="flex items-start gap-4">
                <Mail className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                <div className="min-w-0 flex-1 space-y-1">
                    <p className="font-medium text-sm">{template.event_name}</p>
                    {template.description && (
                        <p className="text-sm text-muted-foreground">
                            {template.description}
                        </p>
                    )}
                    {template.subject && (
                        <p className="truncate text-xs text-muted-foreground">
                            Subject: {template.subject}
                        </p>
                    )}
                    {template.is_customized && template.updated_at && (
                        <p className="text-xs text-muted-foreground">
                            Modified{' '}
                            {new Date(template.updated_at).toLocaleDateString()}
                        </p>
                    )}
                </div>
                <div className="shrink-0">
                    <StatusBadge isCustomized={template.is_customized} />
                </div>
            </div>
        </Link>
    );
}

export default function EmailTemplatesIndex() {
    const { templates } = usePage<PageProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Email Templates" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Email Templates"
                        description="Customize the email templates sent for system events in your organization."
                    />

                    {templates.length === 0 ? (
                        <div className="rounded-lg border border-dashed bg-muted/30 px-6 py-10 text-center">
                            <Mail className="mx-auto mb-3 size-8 text-muted-foreground/50" />
                            <p className="text-sm font-medium">
                                No email templates registered
                            </p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Email templates will appear here when events are
                                registered.
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {templates.map((template) => (
                                <TemplateCard
                                    key={template.event_class}
                                    template={template}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, Eye, RotateCcw, Save } from 'lucide-react';
import { useRef, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import TiptapEditor from '@/components/tiptap-editor';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import {
    index as indexEmailTemplates,
    preview as previewEmailTemplate,
    reset as resetEmailTemplate,
    update as updateEmailTemplate,
} from '@/routes/settings/email-templates';
import { type BreadcrumbItem, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Email Templates', href: '/settings/email-templates' },
    { title: 'Edit', href: '#' },
];

interface PageProps extends SharedData {
    event_class: string;
    event_name: string;
    subject: string;
    body: string;
    is_customized: boolean;
    variables: Record<string, Record<string, string>>;
}

export default function EmailTemplatesEdit() {
    const {
        event_name,
        subject: initialSubject,
        body: initialBody,
        is_customized,
        variables,
    } = usePage<PageProps>().props;

    const { data, setData, put, processing, errors } = useForm({
        subject: initialSubject ?? '',
        body: initialBody ?? '',
    });

    const subjectRef = useRef<HTMLInputElement>(null);
    const [previewHtml, setPreviewHtml] = useState<string | null>(null);
    const [previewing, setPreviewing] = useState(false);
    const [showResetConfirm, setShowResetConfirm] = useState(false);

    const handleSave = (e: React.FormEvent) => {
        e.preventDefault();
        put(updateEmailTemplate.url({ event: event_name }));
    };

    const handlePreview = async () => {
        setPreviewing(true);
        setPreviewHtml(null);
        try {
            const res = await fetch(
                previewEmailTemplate.url({ event: event_name }),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN':
                            (
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                ) as HTMLMetaElement | null
                            )?.content ?? '',
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        subject: data.subject,
                        body: data.body,
                    }),
                },
            );
            const result = (await res.json()) as {
                html?: string;
                error?: string;
            };
            setPreviewHtml(result.html ?? result.error ?? 'Preview unavailable');
        } catch {
            setPreviewHtml('Failed to load preview.');
        } finally {
            setPreviewing(false);
        }
    };

    const handleReset = () => {
        router.delete(resetEmailTemplate.url({ event: event_name }), {
            onSuccess: () => setShowResetConfirm(false),
        });
    };

    const insertVariableIntoSubject = (variable: string) => {
        const input = subjectRef.current;
        if (!input) {
            setData('subject', data.subject + `{{ ${variable} }}`);
            return;
        }
        const start = input.selectionStart ?? data.subject.length;
        const end = input.selectionEnd ?? data.subject.length;
        const token = `{{ ${variable} }}`;
        const newValue =
            data.subject.slice(0, start) + token + data.subject.slice(end);
        setData('subject', newValue);
        // Restore focus + cursor after React re-render
        requestAnimationFrame(() => {
            input.focus();
            const pos = start + token.length;
            input.setSelectionRange(pos, pos);
        });
    };

    const flatVariables = Object.values(variables).reduce<
        Record<string, string>
    >((acc, group) => ({ ...acc, ...group }), {});

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit: ${event_name}`} />

            <SettingsLayout>
                <div className="space-y-6">
                    <div className="flex items-start justify-between gap-4">
                        <HeadingSmall
                            title={`Edit: ${event_name}`}
                            description="Customize the subject and body of this email template."
                        />
                        <Button
                            size="sm"
                            variant="ghost"
                            asChild
                            className="shrink-0"
                        >
                            <Link href={indexEmailTemplates.url()}>
                                <ArrowLeft className="mr-1 size-3" />
                                Back
                            </Link>
                        </Button>
                    </div>

                    <form onSubmit={handleSave} className="space-y-6">
                        {/* Subject */}
                        <div className="space-y-2">
                            <Label htmlFor="subject">Subject</Label>
                            <Input
                                id="subject"
                                ref={subjectRef}
                                value={data.subject}
                                onChange={(e) =>
                                    setData('subject', e.target.value)
                                }
                                placeholder="Email subject line"
                            />
                            {errors.subject && (
                                <p className="text-xs text-destructive">
                                    {errors.subject}
                                </p>
                            )}
                            {Object.keys(flatVariables).length > 0 && (
                                <div className="flex flex-wrap items-center gap-1 pt-1">
                                    <span className="text-xs text-muted-foreground mr-1">
                                        Insert:
                                    </span>
                                    {Object.entries(flatVariables).map(
                                        ([variable, label]) => (
                                            <button
                                                key={variable}
                                                type="button"
                                                onClick={() =>
                                                    insertVariableIntoSubject(
                                                        variable,
                                                    )
                                                }
                                                title={label}
                                                className="inline-flex items-center rounded border bg-muted px-1.5 py-0.5 font-mono text-xs hover:bg-muted/80"
                                            >
                                                {variable}
                                            </button>
                                        ),
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Body editor + preview */}
                        <div className="space-y-2">
                            <Label>Body</Label>
                            <div className="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <TiptapEditor
                                        content={data.body}
                                        onChange={(html) =>
                                            setData('body', html)
                                        }
                                        placeholder="Email body content…"
                                        variables={variables}
                                    />
                                    {errors.body && (
                                        <p className="mt-1 text-xs text-destructive">
                                            {errors.body}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="text-xs text-muted-foreground">
                                            Preview
                                        </span>
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            onClick={handlePreview}
                                            disabled={previewing}
                                            data-pan="email-template-preview"
                                            className="gap-1.5 text-xs"
                                        >
                                            <Eye className="size-3" />
                                            {previewing
                                                ? 'Loading…'
                                                : 'Preview with sample data'}
                                        </Button>
                                    </div>
                                    <div className="min-h-[250px] rounded-md border bg-muted/50 p-4">
                                        {previewHtml ? (
                                            <div
                                                className="prose prose-sm dark:prose-invert"
                                                dangerouslySetInnerHTML={{
                                                    __html: previewHtml,
                                                }}
                                            />
                                        ) : (
                                            <p className="text-xs text-muted-foreground">
                                                Click "Preview with sample data"
                                                to render a preview.
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="flex items-center gap-2">
                            <Button
                                type="submit"
                                size="sm"
                                disabled={processing}
                                data-pan="email-template-save"
                                className="gap-1.5"
                            >
                                <Save className="size-3" />
                                {processing ? 'Saving…' : 'Save template'}
                            </Button>

                            <Button
                                type="button"
                                size="sm"
                                variant="ghost"
                                asChild
                            >
                                <Link href={indexEmailTemplates.url()}>
                                    Cancel
                                </Link>
                            </Button>

                            {is_customized && (
                                <div className="ml-auto">
                                    {showResetConfirm ? (
                                        <div className="flex items-center gap-2">
                                            <span className="text-xs text-muted-foreground">
                                                Reset to default?
                                            </span>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="destructive"
                                                onClick={handleReset}
                                                data-pan="email-template-reset-confirm"
                                                className="gap-1.5 text-xs"
                                            >
                                                Yes, reset
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                onClick={() =>
                                                    setShowResetConfirm(false)
                                                }
                                                className="text-xs"
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    ) : (
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            onClick={() =>
                                                setShowResetConfirm(true)
                                            }
                                            data-pan="email-template-reset"
                                            className="gap-1.5 text-xs text-destructive hover:text-destructive"
                                        >
                                            <RotateCcw className="size-3" />
                                            Reset to default
                                        </Button>
                                    )}
                                </div>
                            )}
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

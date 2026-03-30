import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import {
    index as indexWebhooks,
    regenerateSecret as regenerateSecretWebhook,
    update as updateWebhook,
} from '@/routes/settings/webhooks';
import { type BreadcrumbItem, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Webhooks', href: '/settings/webhooks' },
    { title: 'Edit Webhook', href: '#' },
];

interface EndpointData {
    id: number;
    url: string;
    events: string[];
    is_active: boolean;
    description: string | null;
}

interface PageProps extends SharedData {
    endpoint: EndpointData;
    eventGroups: Record<string, Record<string, string>>;
}

export default function WebhooksEdit() {
    const { endpoint, eventGroups } = usePage<PageProps>().props;

    const { data, setData, put, processing, errors } = useForm<{
        url: string;
        description: string;
        events: string[];
        is_active: boolean;
    }>({
        url: endpoint.url,
        description: endpoint.description ?? '',
        events: endpoint.events,
        is_active: endpoint.is_active,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(updateWebhook.url(endpoint.id));
    };

    const toggleEvent = (eventKey: string) => {
        setData(
            'events',
            data.events.includes(eventKey)
                ? data.events.filter((e) => e !== eventKey)
                : [...data.events, eventKey],
        );
    };

    const toggleGroup = (groupEvents: Record<string, string>) => {
        const keys = Object.keys(groupEvents);
        const allSelected = keys.every((k) => data.events.includes(k));
        if (allSelected) {
            setData(
                'events',
                data.events.filter((e) => !keys.includes(e)),
            );
        } else {
            const newEvents = [...data.events];
            for (const k of keys) {
                if (!newEvents.includes(k)) {
                    newEvents.push(k);
                }
            }
            setData('events', newEvents);
        }
    };

    const handleRegenerateSecret = () => {
        if (
            confirm(
                'Regenerate the signing secret? Any existing integrations using the current secret will stop verifying.',
            )
        ) {
            router.post(regenerateSecretWebhook.url(endpoint.id));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Webhook" />

            <SettingsLayout>
                <div className="space-y-6">
                    <div className="flex items-start justify-between">
                        <HeadingSmall
                            title="Edit Webhook Endpoint"
                            description="Update the URL and events for this webhook."
                        />
                        <Button
                            size="sm"
                            variant="outline"
                            onClick={handleRegenerateSecret}
                            data-pan="webhook-regenerate-secret"
                            className="shrink-0 gap-1.5 text-xs"
                        >
                            <KeyRound className="size-3" />
                            Regenerate Secret
                        </Button>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="url">
                                URL{' '}
                                <span className="text-destructive">*</span>
                            </Label>
                            <Input
                                id="url"
                                type="url"
                                value={data.url}
                                onChange={(e) =>
                                    setData('url', e.target.value)
                                }
                                placeholder="https://example.com/webhooks"
                                className="font-mono"
                                required
                            />
                            <InputError message={errors.url} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">
                                Description{' '}
                                <span className="text-xs text-muted-foreground">
                                    (optional)
                                </span>
                            </Label>
                            <Input
                                id="description"
                                type="text"
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                                placeholder="e.g. Production notification handler"
                            />
                            <InputError message={errors.description} />
                        </div>

                        <div className="space-y-4">
                            <div>
                                <Label>Events</Label>
                                <p className="mt-0.5 text-sm text-muted-foreground">
                                    Select which events should trigger this
                                    webhook.
                                </p>
                            </div>
                            <InputError message={errors.events} />

                            {Object.entries(eventGroups).map(
                                ([group, events]) => {
                                    const keys = Object.keys(events);
                                    const allSelected = keys.every((k) =>
                                        data.events.includes(k),
                                    );
                                    const someSelected = keys.some((k) =>
                                        data.events.includes(k),
                                    );

                                    return (
                                        <div key={group} className="space-y-2">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    toggleGroup(events)
                                                }
                                                className="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-muted-foreground hover:text-foreground"
                                                data-pan="webhook-toggle-group"
                                            >
                                                <Checkbox
                                                    checked={allSelected}
                                                    data-state={
                                                        someSelected &&
                                                        !allSelected
                                                            ? 'indeterminate'
                                                            : undefined
                                                    }
                                                    className="pointer-events-none size-3.5"
                                                />
                                                {group}
                                            </button>
                                            <div className="ml-5 space-y-2 rounded-lg border bg-muted/30 p-3">
                                                {Object.entries(events).map(
                                                    ([key, label]) => (
                                                        <label
                                                            key={key}
                                                            className="flex cursor-pointer items-center gap-3"
                                                        >
                                                            <Checkbox
                                                                checked={data.events.includes(
                                                                    key,
                                                                )}
                                                                onCheckedChange={() =>
                                                                    toggleEvent(
                                                                        key,
                                                                    )
                                                                }
                                                                data-pan="webhook-toggle-event"
                                                            />
                                                            <div className="min-w-0">
                                                                <span className="font-mono text-xs text-foreground">
                                                                    {key}
                                                                </span>
                                                                <p className="text-xs text-muted-foreground">
                                                                    {label}
                                                                </p>
                                                            </div>
                                                        </label>
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                    );
                                },
                            )}
                        </div>

                        <div className="flex items-center gap-3">
                            <Switch
                                id="is_active"
                                checked={data.is_active}
                                onCheckedChange={(checked) =>
                                    setData('is_active', checked)
                                }
                                data-pan="webhook-toggle-active"
                            />
                            <Label htmlFor="is_active">
                                Enable this webhook
                            </Label>
                        </div>

                        <div className="flex gap-2 pt-2">
                            <Button
                                type="submit"
                                disabled={processing || !data.url}
                                data-pan="webhook-edit-submit"
                            >
                                {processing ? 'Saving…' : 'Save Changes'}
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={indexWebhooks.url()}>Cancel</Link>
                            </Button>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

import { Head, Link, useForm, usePage } from '@inertiajs/react';

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
    store as storeWebhook,
} from '@/routes/settings/webhooks';
import { type BreadcrumbItem, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Webhooks', href: '/settings/webhooks' },
    { title: 'Add Webhook', href: '/settings/webhooks/create' },
];

interface PageProps extends SharedData {
    eventGroups: Record<string, Record<string, string>>;
}

export default function WebhooksCreate() {
    const { eventGroups } = usePage<PageProps>().props;

    const { data, setData, post, processing, errors } = useForm<{
        url: string;
        description: string;
        events: string[];
        is_active: boolean;
    }>({
        url: '',
        description: '',
        events: [],
        is_active: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(storeWebhook.url());
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add Webhook" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Add Webhook Endpoint"
                        description="Configure a URL to receive event notifications."
                    />

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
                                autoFocus
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
                                data-pan="webhook-create-submit"
                            >
                                {processing
                                    ? 'Creating…'
                                    : 'Create Webhook'}
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

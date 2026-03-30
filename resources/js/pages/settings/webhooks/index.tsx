import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    Activity,
    AlertTriangle,
    CheckCircle2,
    Edit,
    PauseCircle,
    Plus,
    RefreshCw,
    Trash2,
    Webhook,
    Zap,
} from 'lucide-react';
import { useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import {
    create as createWebhook,
    destroy as destroyWebhook,
    edit as editWebhook,
    resetCircuit as resetCircuitWebhook,
    test as testWebhook,
} from '@/routes/settings/webhooks';
import { type BreadcrumbItem, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Webhooks', href: '/settings/webhooks' },
];

interface WebhookEndpoint {
    id: number;
    url: string;
    events: string[];
    is_active: boolean;
    description: string | null;
    last_called_at: string | null;
    circuit_state: 'healthy' | 'tripped' | 'recovering';
    created_at: string;
}

interface PageProps extends SharedData {
    endpoints: WebhookEndpoint[];
    eventGroups: Record<string, Record<string, string>>;
}

type CircuitState = 'healthy' | 'tripped' | 'recovering' | 'disabled';

function StatusBadge({
    isActive,
    circuitState,
}: {
    isActive: boolean;
    circuitState: WebhookEndpoint['circuit_state'];
}) {
    if (!isActive) {
        return (
            <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                <PauseCircle className="size-3" />
                Disabled
            </span>
        );
    }

    const config: Record<
        CircuitState,
        { label: string; icon: React.ElementType; className: string }
    > = {
        healthy: {
            label: 'Healthy',
            icon: CheckCircle2,
            className:
                'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400',
        },
        tripped: {
            label: 'Tripped',
            icon: AlertTriangle,
            className:
                'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400',
        },
        recovering: {
            label: 'Recovering',
            icon: Activity,
            className:
                'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        },
        disabled: {
            label: 'Disabled',
            icon: PauseCircle,
            className:
                'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        },
    };

    const c = config[circuitState] ?? config.healthy;
    const Icon = c.icon;

    return (
        <span
            className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ${c.className}`}
        >
            <Icon className="size-3" />
            {c.label}
        </span>
    );
}

function EndpointCard({ endpoint }: { endpoint: WebhookEndpoint }) {
    const [testResult, setTestResult] = useState<{
        success: boolean;
        message: string;
    } | null>(null);
    const [testing, setTesting] = useState(false);

    const handleTest = async () => {
        setTesting(true);
        setTestResult(null);
        try {
            const res = await fetch(testWebhook.url(endpoint.id), {
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
            });
            const data = (await res.json()) as { message?: string };
            setTestResult({
                success: res.ok,
                message: data.message ?? (res.ok ? 'Ping sent' : 'Ping failed'),
            });
        } catch {
            setTestResult({ success: false, message: 'Request failed' });
        } finally {
            setTesting(false);
        }
    };

    const handleDelete = () => {
        if (confirm(`Delete webhook for ${endpoint.url}?`)) {
            router.delete(destroyWebhook.url(endpoint.id));
        }
    };

    const handleResetCircuit = () => {
        if (confirm('Reset the circuit breaker for this endpoint?')) {
            router.post(resetCircuitWebhook.url(endpoint.id));
        }
    };

    const canReset =
        endpoint.circuit_state === 'tripped' ||
        endpoint.circuit_state === 'recovering';

    return (
        <div className="rounded-lg border bg-muted/50">
            <div className="flex items-start gap-4 px-4 py-3">
                <Webhook className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                <div className="min-w-0 flex-1 space-y-1">
                    <p className="truncate font-mono text-sm font-medium">
                        {endpoint.url}
                    </p>
                    {endpoint.description && (
                        <p className="text-sm text-muted-foreground">
                            {endpoint.description}
                        </p>
                    )}
                    <div className="flex flex-wrap items-center gap-2 pt-0.5">
                        <span className="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground">
                            {endpoint.events.length === 0
                                ? 'No events'
                                : `${endpoint.events.length} event${endpoint.events.length === 1 ? '' : 's'}`}
                        </span>
                        {endpoint.last_called_at && (
                            <span className="text-xs text-muted-foreground">
                                Last called{' '}
                                {new Date(
                                    endpoint.last_called_at,
                                ).toLocaleDateString()}
                            </span>
                        )}
                    </div>
                    {testResult && (
                        <p
                            className={`text-xs ${testResult.success ? 'text-green-600 dark:text-green-400' : 'text-destructive'}`}
                        >
                            {testResult.message}
                        </p>
                    )}
                </div>
                <div className="flex shrink-0 items-center gap-1">
                    <StatusBadge
                        isActive={endpoint.is_active}
                        circuitState={endpoint.circuit_state}
                    />
                </div>
            </div>
            <div className="flex items-center gap-1 border-t px-4 py-2">
                <Button
                    size="sm"
                    variant="ghost"
                    onClick={handleTest}
                    disabled={testing}
                    data-pan="webhook-test-ping"
                    className="gap-1.5 text-xs"
                >
                    <Zap className="size-3" />
                    {testing ? 'Sending…' : 'Test ping'}
                </Button>
                {canReset && (
                    <Button
                        size="sm"
                        variant="ghost"
                        onClick={handleResetCircuit}
                        data-pan="webhook-reset-circuit"
                        className="gap-1.5 text-xs text-amber-600 hover:text-amber-600 dark:text-amber-400"
                    >
                        <RefreshCw className="size-3" />
                        Reset circuit
                    </Button>
                )}
                <div className="ml-auto flex items-center gap-1">
                    <Button
                        size="sm"
                        variant="ghost"
                        asChild
                        data-pan="webhook-edit"
                    >
                        <Link href={editWebhook.url(endpoint.id)}>
                            <Edit className="size-3" />
                            <span className="sr-only">Edit</span>
                        </Link>
                    </Button>
                    <Button
                        size="sm"
                        variant="ghost"
                        onClick={handleDelete}
                        data-pan="webhook-delete"
                        className="text-destructive hover:text-destructive"
                    >
                        <Trash2 className="size-3" />
                        <span className="sr-only">Delete</span>
                    </Button>
                </div>
            </div>
        </div>
    );
}

export default function WebhooksIndex() {
    const { endpoints } = usePage<PageProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Webhooks" />

            <SettingsLayout>
                <div className="space-y-6">
                    <div className="flex items-start justify-between">
                        <HeadingSmall
                            title="Webhook Endpoints"
                            description="Receive HTTP POST notifications when events occur in your organization."
                        />
                        <Button
                            size="sm"
                            asChild
                            data-pan="webhook-add"
                            className="shrink-0"
                        >
                            <Link href={createWebhook.url()}>
                                <Plus className="mr-1 size-3" />
                                Add Webhook
                            </Link>
                        </Button>
                    </div>

                    {endpoints.length === 0 ? (
                        <div className="rounded-lg border border-dashed bg-muted/30 px-6 py-10 text-center">
                            <Webhook className="mx-auto mb-3 size-8 text-muted-foreground/50" />
                            <p className="text-sm font-medium">
                                No webhook endpoints
                            </p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Add a webhook to start receiving event
                                notifications.
                            </p>
                            <Button
                                size="sm"
                                asChild
                                className="mt-4"
                                data-pan="webhook-add-empty"
                            >
                                <Link href={createWebhook.url()}>
                                    <Plus className="mr-1 size-3" />
                                    Add Webhook
                                </Link>
                            </Button>
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {endpoints.map((endpoint) => (
                                <EndpointCard
                                    key={endpoint.id}
                                    endpoint={endpoint}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

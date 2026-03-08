import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    AlertCircle,
    CheckCircle,
    Clock,
    Globe,
    RefreshCw,
    Trash2,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import {
    destroy as destroyDomain,
    store as storeDomain,
    verify as verifyDomain,
} from '@/routes/settings/domains';
import { type BreadcrumbItem, type OrgDomain, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Domains', href: '/settings/domains' },
];

interface PageProps extends SharedData {
    organization: {
        id: number;
        name: string;
        slug: string;
    } | null;
    domains: OrgDomain[];
    baseDomain: string | null;
}

function StatusBadge({ status }: { status: OrgDomain['status'] }) {
    const config = (
        {
            pending_dns: {
                label: 'Pending DNS',
                icon: Clock,
                className:
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
            },
            dns_verified: {
                label: 'DNS Verified',
                icon: CheckCircle,
                className:
                    'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            },
            ssl_provisioning: {
                label: 'SSL Provisioning',
                icon: RefreshCw,
                className:
                    'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            },
            active: {
                label: 'Active',
                icon: CheckCircle,
                className:
                    'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            },
            error: {
                label: 'Error',
                icon: XCircle,
                className:
                    'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            },
            expired: {
                label: 'Expired',
                icon: AlertCircle,
                className:
                    'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
            },
        } as const
    )[status] ?? {
        label: status,
        icon: AlertCircle,
        className: 'bg-gray-100 text-gray-800',
    };

    const Icon = config.icon;

    return (
        <span
            className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ${config.className}`}
        >
            <Icon className="size-3" />
            {config.label}
        </span>
    );
}

function DomainRow({ domain }: { domain: OrgDomain }) {
    const [expanded, setExpanded] = useState(false);

    const handleVerify = () => {
        router.post(verifyDomain.url(domain.id));
    };

    const handleDelete = () => {
        if (confirm(`Remove domain ${domain.domain}?`)) {
            router.delete(destroyDomain.url(domain.id));
        }
    };

    const cnameHost = domain.domain.includes('.')
        ? domain.domain.split('.')[0]
        : domain.domain;

    return (
        <div className="rounded-lg border">
            <div className="flex items-center gap-4 px-4 py-3">
                <Globe className="size-4 shrink-0 text-muted-foreground" />
                <div className="min-w-0 flex-1">
                    <p className="truncate font-mono text-sm font-medium">
                        {domain.domain}
                    </p>
                    {domain.status === 'error' && domain.failure_reason && (
                        <p className="mt-0.5 text-xs text-destructive">
                            {domain.failure_reason ===
                            'cloudflare_proxy_detected'
                                ? 'Cloudflare proxy detected — disable orange cloud (proxy) in Cloudflare DNS'
                                : domain.failure_reason === 'timeout'
                                  ? 'DNS verification timed out'
                                  : domain.failure_reason}
                        </p>
                    )}
                    {domain.ssl_expires_at && domain.status === 'active' && (
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            SSL expires{' '}
                            {new Date(
                                domain.ssl_expires_at,
                            ).toLocaleDateString()}
                        </p>
                    )}
                </div>
                <StatusBadge status={domain.status} />
                <div className="flex items-center gap-2">
                    {domain.status === 'pending_dns' && (
                        <Button
                            size="sm"
                            variant="ghost"
                            onClick={() => setExpanded(!expanded)}
                        >
                            {expanded ? 'Hide' : 'Setup'}
                        </Button>
                    )}
                    {(domain.status === 'pending_dns' ||
                        domain.status === 'error') && (
                        <Button
                            size="sm"
                            variant="outline"
                            onClick={handleVerify}
                        >
                            <RefreshCw className="mr-1 size-3" />
                            Verify
                        </Button>
                    )}
                    <Button
                        size="sm"
                        variant="ghost"
                        onClick={handleDelete}
                        className="text-destructive hover:text-destructive"
                    >
                        <Trash2 className="size-3" />
                    </Button>
                </div>
            </div>

            {expanded &&
                domain.status === 'pending_dns' &&
                domain.cname_target && (
                    <div className="space-y-3 border-t bg-muted/30 px-4 py-3">
                        <p className="text-xs font-medium text-muted-foreground">
                            Add this CNAME record to your DNS provider:
                        </p>
                        <div className="grid grid-cols-3 gap-2 text-xs">
                            <div>
                                <p className="mb-1 font-medium text-muted-foreground">
                                    Type
                                </p>
                                <code className="block rounded border bg-background px-2 py-1">
                                    CNAME
                                </code>
                            </div>
                            <div>
                                <p className="mb-1 font-medium text-muted-foreground">
                                    Name / Host
                                </p>
                                <code className="block rounded border bg-background px-2 py-1">
                                    {cnameHost}
                                </code>
                            </div>
                            <div>
                                <p className="mb-1 font-medium text-muted-foreground">
                                    Value / Target
                                </p>
                                <code className="block rounded border bg-background px-2 py-1">
                                    {domain.cname_target}
                                </code>
                            </div>
                        </div>
                        {domain.failure_reason ===
                            'cloudflare_proxy_detected' && (
                            <div className="rounded border border-orange-200 bg-orange-50 p-3 dark:bg-orange-950/20">
                                <p className="text-xs font-medium text-orange-800 dark:text-orange-200">
                                    Cloudflare users
                                </p>
                                <p className="mt-1 text-xs text-orange-700 dark:text-orange-300">
                                    Make sure the DNS record is set to "DNS
                                    only" (gray cloud), not "Proxied" (orange
                                    cloud).
                                </p>
                            </div>
                        )}
                    </div>
                )}
        </div>
    );
}

export default function DomainsSettings() {
    const { organization, domains, baseDomain } = usePage<PageProps>().props;

    const { data, setData, post, processing, errors, reset } = useForm({
        domain: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(storeDomain.url(), {
            onSuccess: () => reset(),
        });
    };

    const workspaceUrl =
        organization && baseDomain
            ? `${organization.slug}.${baseDomain}`
            : null;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Domains" />

            <SettingsLayout>
                <div className="space-y-8">
                    <div className="space-y-4">
                        <HeadingSmall
                            title="Workspace URL"
                            description="Your organization's subdomain on the platform."
                        />
                        {workspaceUrl && (
                            <div className="flex items-center gap-3 rounded-lg border bg-muted/50 px-4 py-3">
                                <Globe className="size-4 text-muted-foreground" />
                                <span className="font-mono text-sm">
                                    {workspaceUrl}
                                </span>
                                <span className="ml-auto inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                    <CheckCircle className="size-3" />
                                    Active
                                </span>
                            </div>
                        )}
                    </div>

                    <div className="space-y-4">
                        <HeadingSmall
                            title="Custom Domains"
                            description="Add a custom domain to access your workspace."
                        />

                        {domains.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No custom domains configured.
                            </p>
                        ) : (
                            <div className="space-y-3">
                                {domains.map((domain) => (
                                    <DomainRow
                                        key={domain.id}
                                        domain={domain}
                                    />
                                ))}
                            </div>
                        )}

                        <form
                            onSubmit={handleSubmit}
                            className="space-y-3 pt-2"
                        >
                            <div className="space-y-2">
                                <Label htmlFor="domain">
                                    Add custom domain
                                </Label>
                                <div className="flex gap-2">
                                    <Input
                                        id="domain"
                                        type="text"
                                        value={data.domain}
                                        onChange={(e) =>
                                            setData('domain', e.target.value)
                                        }
                                        placeholder="support.yourcompany.com"
                                        className="max-w-xs font-mono"
                                    />
                                    <Button
                                        type="submit"
                                        disabled={processing || !data.domain}
                                    >
                                        {processing
                                            ? 'Adding...'
                                            : 'Add Domain'}
                                    </Button>
                                </div>
                                <InputError message={errors.domain} />
                            </div>
                        </form>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

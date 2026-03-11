import { Head, useForm, usePage } from '@inertiajs/react';
import { Globe } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { update as updateSlug } from '@/routes/settings/general/slug';
import { type BreadcrumbItem, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Workspace URL', href: '/settings/general' },
];

interface PageProps extends SharedData {
    organization: {
        id: number;
        name: string;
        slug: string;
    } | null;
    baseDomain: string | null;
}

interface AvailabilityResult {
    available: boolean;
    reserved: boolean;
    taken: boolean;
    suggestion: string | null;
}

export default function SettingsGeneral() {
    const { organization, baseDomain } = usePage<PageProps>().props;

    const { data, setData, patch, processing, errors, reset } = useForm({
        slug: organization?.slug ?? '',
        confirmed: false,
    });

    const [availability, setAvailability] = useState<AvailabilityResult | null>(
        null,
    );
    const [checking, setChecking] = useState(false);
    const debounceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const checkAvailability = useCallback(
        (slug: string) => {
            if (debounceTimerRef.current) {
                clearTimeout(debounceTimerRef.current);
            }
            if (!slug || slug === organization?.slug) {
                setAvailability(null);
                return;
            }
            debounceTimerRef.current = setTimeout(async () => {
                setChecking(true);
                try {
                    const res = await fetch(
                        `/api/slug-availability?slug=${encodeURIComponent(slug)}`,
                        {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        },
                    );
                    const json: AvailabilityResult = await res.json();
                    setAvailability(json);
                } catch {
                    setAvailability(null);
                } finally {
                    setChecking(false);
                }
            }, 500);
        },
        [organization?.slug],
    );

    const handleSlugChange = (value: string) => {
        const normalized = value.toLowerCase().replace(/[^a-z0-9-]/g, '');
        setData('slug', normalized);
        setAvailability(null);
        checkAvailability(normalized);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(updateSlug.url(), {
            onSuccess: () => reset('confirmed'),
        });
    };

    const currentUrl =
        organization && baseDomain
            ? `${organization.slug}.${baseDomain}`
            : null;
    const newUrl =
        data.slug && baseDomain ? `${data.slug}.${baseDomain}` : null;
    const isUnchanged = data.slug === organization?.slug;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Workspace URL" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Workspace URL"
                        description="Your organization's unique URL on the platform."
                    />

                    {currentUrl && (
                        <div className="flex items-center gap-2 rounded-lg border bg-muted/50 px-4 py-3">
                            <Globe className="size-4 text-muted-foreground" />
                            <span className="font-mono text-sm">
                                {currentUrl}
                            </span>
                            <button
                                type="button"
                                className="ml-auto text-xs text-muted-foreground hover:text-foreground"
                                onClick={() =>
                                    navigator.clipboard.writeText(
                                        `https://${currentUrl}`,
                                    )
                                }
                            >
                                Copy
                            </button>
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="slug">New workspace URL</Label>
                            <div className="flex items-center gap-2">
                                <Input
                                    id="slug"
                                    type="text"
                                    value={data.slug}
                                    onChange={(e) =>
                                        handleSlugChange(e.target.value)
                                    }
                                    placeholder="your-org-slug"
                                    className="max-w-xs font-mono"
                                    autoComplete="off"
                                />
                                {baseDomain && (
                                    <span className="text-sm text-muted-foreground">
                                        .{baseDomain}
                                    </span>
                                )}
                            </div>
                            {checking && (
                                <p className="text-xs text-muted-foreground">
                                    Checking availability...
                                </p>
                            )}
                            {!checking && availability && !isUnchanged && (
                                <p
                                    className={`text-xs ${availability.available ? 'text-green-600' : 'text-destructive'}`}
                                >
                                    {availability.available
                                        ? '✓ Available'
                                        : availability.reserved
                                          ? '✗ This slug is reserved'
                                          : availability.taken
                                            ? `✗ Already taken${availability.suggestion ? ` — try "${availability.suggestion}"` : ''}`
                                            : '✗ Not available'}
                                </p>
                            )}
                            <InputError message={errors.slug} />
                        </div>

                        {!isUnchanged &&
                            data.slug &&
                            availability?.available && (
                                <div className="space-y-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/20">
                                    <p className="text-sm font-medium text-amber-800 dark:text-amber-200">
                                        Before you continue
                                    </p>
                                    <ul className="list-inside list-disc space-y-1 text-sm text-amber-700 dark:text-amber-300">
                                        <li>
                                            Webhook URLs containing the old slug
                                            must be updated
                                        </li>
                                        <li>
                                            SSO / OAuth redirect URIs must be
                                            updated
                                        </li>
                                        <li>
                                            The old URL will redirect while
                                            unclaimed
                                        </li>
                                    </ul>
                                    <label className="flex cursor-pointer items-center gap-2">
                                        <input
                                            type="checkbox"
                                            checked={data.confirmed}
                                            onChange={(e) =>
                                                setData(
                                                    'confirmed',
                                                    e.target.checked,
                                                )
                                            }
                                            className="rounded"
                                        />
                                        <span className="text-sm text-amber-700 dark:text-amber-300">
                                            I understand the consequences of
                                            changing my workspace URL
                                        </span>
                                    </label>
                                    {newUrl && (
                                        <p className="text-xs text-amber-600 dark:text-amber-400">
                                            New URL:{' '}
                                            <span className="font-mono">
                                                {newUrl}
                                            </span>
                                        </p>
                                    )}
                                    <InputError message={errors.confirmed} />
                                </div>
                            )}

                        <Button
                            type="submit"
                            disabled={
                                processing ||
                                isUnchanged ||
                                !availability?.available ||
                                !data.confirmed
                            }
                        >
                            {processing ? 'Saving...' : 'Update Workspace URL'}
                        </Button>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

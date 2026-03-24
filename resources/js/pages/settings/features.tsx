import { Head, router, usePage } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface FeatureEntry {
    key: string;
    plan_required: string | null;
    override: 'inherit' | 'enabled' | 'disabled';
}

interface PageProps extends Omit<SharedData, 'features'> {
    features: FeatureEntry[];
    orgPlan: string | null;
    planFeatures: Record<string, string[]>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Feature Settings', href: '/settings/features' },
];

const FEATURE_LABELS: Record<string, string> = {
    blog: 'Blog',
    changelog: 'Changelog',
    help: 'Help Center',
    contact: 'Contact Form',
    onboarding: 'Onboarding',
    appearance_settings: 'Appearance Settings',
    gamification: 'Gamification',
    api_access: 'API Access',
    crm: 'CRM',
    reports: 'Reports',
    dashboards: 'Dashboards',
    workflows: 'Workflows',
    announcements: 'Announcements',
    billing: 'Billing',
};

const OVERRIDE_OPTIONS = [
    { value: 'inherit', label: 'Inherit (app default)' },
    { value: 'enabled', label: 'Enabled' },
    { value: 'disabled', label: 'Disabled' },
] as const;

function hasPlanAccess(
    featureKey: string,
    planRequired: string | null,
    orgPlan: string | null,
    planFeatures: Record<string, string[]>,
): boolean {
    if (!planRequired) return true;
    if (!orgPlan) return false;
    return (planFeatures[orgPlan] ?? []).includes(featureKey);
}

function FeatureRow({ feature, locked }: { feature: FeatureEntry; locked: boolean }) {
    const label = FEATURE_LABELS[feature.key] ?? feature.key;

    const handleChange = (override: string) => {
        if (locked && override === 'enabled') return;
        router.post(
            '/settings/features',
            { key: feature.key, override },
            { preserveScroll: true },
        );
    };

    return (
        <div className={`flex items-center justify-between gap-4 py-3 ${locked ? 'opacity-60' : ''}`}>
            <div className="min-w-0 flex-1">
                <p className="text-sm font-medium">{label}</p>
                <div className="mt-0.5 flex items-center gap-2">
                    {feature.plan_required && (
                        <span className="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                            {feature.plan_required}
                        </span>
                    )}
                    <span className="font-mono text-xs text-muted-foreground">
                        {feature.key}
                    </span>
                </div>
                {locked && (
                    <p className="mt-1 text-xs text-amber-600 dark:text-amber-400">
                        Upgrade to <strong>{feature.plan_required}</strong> to enable this feature.
                    </p>
                )}
            </div>
            <div className="flex shrink-0 gap-1 rounded-lg border bg-muted/40 p-0.5">
                {OVERRIDE_OPTIONS.map((opt) => (
                    <button
                        key={opt.value}
                        type="button"
                        onClick={() => handleChange(opt.value)}
                        disabled={locked && opt.value === 'enabled'}
                        className={`rounded-md px-3 py-1.5 text-xs font-medium transition-colors ${
                            feature.override === opt.value
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        } ${locked && opt.value === 'enabled' ? 'cursor-not-allowed opacity-40' : ''}`}
                    >
                        {opt.label}
                    </button>
                ))}
            </div>
        </div>
    );
}

export default function Features() {
    const { features, orgPlan, planFeatures } = usePage<PageProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Feature Settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Feature Settings"
                        description="Control which features are enabled or disabled for your organization. 'Inherit' uses the app-wide default."
                    />

                    {features.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No delegatable features are available.
                        </p>
                    ) : (
                        <div className="divide-y rounded-lg border">
                            {features.map((feature) => (
                                <div key={feature.key} className="px-4">
                                    <FeatureRow
                                        feature={feature}
                                        locked={!hasPlanAccess(feature.key, feature.plan_required, orgPlan, planFeatures)}
                                    />
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

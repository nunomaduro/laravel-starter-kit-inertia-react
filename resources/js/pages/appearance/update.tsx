import { Head, usePage } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';
import { cn } from '@/lib/utils';
import { type ThemePreset, useThemePreset } from '@/hooks/use-appearance';
import { type BreadcrumbItem, type SharedData } from '@/types';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit as editAppearance } from '@/routes/appearance';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: editAppearance().url,
    },
];

const presets: {
    value: ThemePreset;
    label: string;
    swatch: string;
    swatchDark: string;
}[] = [
    {
        value: 'default',
        label: 'Default',
        swatch: 'oklch(0.205 0 0)',
        swatchDark: 'oklch(0.985 0 0)',
    },
    {
        value: 'vega',
        label: 'Vega',
        swatch: 'oklch(0.45 0.15 262)',
        swatchDark: 'oklch(0.75 0.12 262)',
    },
    {
        value: 'nova',
        label: 'Nova',
        swatch: 'oklch(0.55 0.18 45)',
        swatchDark: 'oklch(0.78 0.14 45)',
    },
];

function ThemePresetPicker() {
    const { preset, updatePreset } = useThemePreset();

    return (
        <div className="flex flex-wrap gap-3">
            {presets.map((p) => (
                <button
                    key={p.value}
                    type="button"
                    onClick={() => updatePreset(p.value)}
                    data-pan={`appearance-theme-${p.value}`}
                    className={cn(
                        'flex cursor-pointer flex-col items-center gap-2 rounded-lg border-2 p-3 text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                        preset === p.value
                            ? 'border-primary bg-primary/5'
                            : 'border-border hover:border-primary/50 hover:bg-accent/50',
                    )}
                >
                    <span
                        className="block h-8 w-8 rounded-full ring-1 ring-border dark:hidden"
                        style={{ background: p.swatch }}
                    />
                    <span
                        className="hidden h-8 w-8 rounded-full ring-1 ring-border dark:block"
                        style={{ background: p.swatchDark }}
                    />
                    <span>{p.label}</span>
                </button>
            ))}
        </div>
    );
}

export default function Update() {
    const { branding } = usePage<SharedData>().props;
    const allowUserCustomization = branding?.allowUserCustomization ?? true;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Appearance settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Appearance settings"
                        description="Update your account's appearance settings"
                    />
                    {!allowUserCustomization ? (
                        <p className="text-sm text-muted-foreground">
                            Your organization has disabled appearance
                            customization. Theme is set by your organization.
                        </p>
                    ) : (
                        <div className="space-y-6">
                            <div className="space-y-2">
                                <p className="text-sm font-medium">Color theme</p>
                                <ThemePresetPicker />
                            </div>
                            <div className="space-y-2">
                                <p className="text-sm font-medium">Mode</p>
                                <AppearanceTabs />
                            </div>
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

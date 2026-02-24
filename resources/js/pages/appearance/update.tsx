import { Head, usePage } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';
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
                        <AppearanceTabs />
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

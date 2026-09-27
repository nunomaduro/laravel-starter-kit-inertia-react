import { Head } from '@inertiajs/react';
import type { Props as ManagePasskeysProps } from '@/components/manage-passkeys';
import ManagePasskeys from '@/components/manage-passkeys';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { index } from '@/routes/user-passkey';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Passkeys',
        href: index(),
    },
];

export default function Index({
    canManagePasskeys,
    passkeys,
}: ManagePasskeysProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Passkeys" />

            <SettingsLayout>
                <ManagePasskeys
                    canManagePasskeys={canManagePasskeys}
                    passkeys={passkeys}
                />
            </SettingsLayout>
        </AppLayout>
    );
}

import { Head, useForm, usePage } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { update as updateNotifications } from '@/routes/settings/notifications';
import { type BreadcrumbItem, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Notifications', href: '/settings/notifications' },
];

interface NotificationPref {
    key: string;
    label: string;
    channels: string[];
    via_database: boolean;
    via_email: boolean;
}

interface PageProps extends SharedData {
    preferences: NotificationPref[];
}

export default function NotificationSettings() {
    const { preferences } = usePage<PageProps>().props;

    const { data, setData, patch, processing } = useForm({
        preferences: preferences,
    });

    const updatePref = (
        index: number,
        channel: 'via_database' | 'via_email',
        value: boolean,
    ) => {
        const updated = [...data.preferences];
        updated[index] = { ...updated[index], [channel]: value };
        setData('preferences', updated);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(updateNotifications.url());
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notification Preferences" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Notification Preferences"
                        description="Choose which notifications you want to receive and how."
                    />

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="rounded-lg border">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b bg-muted/50 text-left text-xs font-medium text-muted-foreground">
                                        <th className="px-4 py-3">
                                            Notification
                                        </th>
                                        <th className="px-4 py-3 text-center">
                                            In-App
                                        </th>
                                        <th className="px-4 py-3 text-center">
                                            Email
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {data.preferences.map((pref, index) => (
                                        <tr
                                            key={pref.key}
                                            className="hover:bg-muted/30"
                                        >
                                            <td className="px-4 py-3 font-medium">
                                                {pref.label}
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                {pref.channels.includes(
                                                    'database',
                                                ) ? (
                                                    <input
                                                        type="checkbox"
                                                        checked={
                                                            pref.via_database
                                                        }
                                                        onChange={(e) =>
                                                            updatePref(
                                                                index,
                                                                'via_database',
                                                                e.target
                                                                    .checked,
                                                            )
                                                        }
                                                        className="rounded"
                                                    />
                                                ) : (
                                                    <span className="text-muted-foreground">
                                                        —
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                {pref.channels.includes(
                                                    'email',
                                                ) ? (
                                                    <input
                                                        type="checkbox"
                                                        checked={pref.via_email}
                                                        onChange={(e) =>
                                                            updatePref(
                                                                index,
                                                                'via_email',
                                                                e.target
                                                                    .checked,
                                                            )
                                                        }
                                                        className="rounded"
                                                    />
                                                ) : (
                                                    <span className="text-muted-foreground">
                                                        —
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Preferences'}
                        </Button>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
